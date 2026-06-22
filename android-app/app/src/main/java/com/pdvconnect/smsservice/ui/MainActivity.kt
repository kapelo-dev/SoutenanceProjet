package com.pdvconnect.smsservice.ui

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.util.TypedValue
import android.view.Gravity
import android.view.View
import android.widget.Button
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.PopupMenu
import androidx.core.content.ContextCompat
import androidx.lifecycle.lifecycleScope
import com.google.android.material.card.MaterialCardView
import com.google.android.material.textfield.TextInputEditText
import com.pdvconnect.smsservice.R
import com.pdvconnect.smsservice.api.AgentApiClient
import com.pdvconnect.smsservice.api.AgentBalances
import com.pdvconnect.smsservice.api.AgentCancelRequest
import com.pdvconnect.smsservice.api.AgentChangePasswordRequest
import com.pdvconnect.smsservice.api.AgentDashboard
import com.pdvconnect.smsservice.api.AgentInfo
import com.pdvconnect.smsservice.api.AgentLoginRequest
import com.pdvconnect.smsservice.api.AgentLoginResponse
import com.pdvconnect.smsservice.api.AgentTransaction
import com.pdvconnect.smsservice.api.OperateurStats
import com.pdvconnect.smsservice.api.MobileConfigApiClient
import com.pdvconnect.smsservice.api.VerifyConfigCodeRequest
import com.pdvconnect.smsservice.data.AgentDashboardCache
import com.pdvconnect.smsservice.data.AppPreferences
import com.pdvconnect.smsservice.databinding.ActivityMainBinding
import com.pdvconnect.smsservice.sms.ServiceStarter
import com.pdvconnect.smsservice.sms.SmsForwarderService
import com.pdvconnect.smsservice.sync.OfflineSyncRepository
import com.pdvconnect.smsservice.sync.SyncScheduler
import com.pdvconnect.smsservice.util.AppUpdateChecker
import com.pdvconnect.smsservice.util.NetworkUtils
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import java.io.IOException
import java.text.NumberFormat
import java.util.Locale

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var prefs: AppPreferences
    private lateinit var dashboardCache: AgentDashboardCache
    private lateinit var syncRepository: OfflineSyncRepository

    private var smsConfigUnlockedThisSession = false
    private var agentToken: String? = null
    private var currentAgent: AgentInfo? = null
    private var showingOfflineCache = false
    private var showingServerUnreachable = false
    private var pendingUpdateResult: AppUpdateChecker.Result? = null
    private var configAccessDialog: AlertDialog? = null

    private val requestPermissions = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { result ->
        if (!result.values.all { it }) {
            Toast.makeText(this, "Les permissions SMS sont nécessaires pour transférer les transactions.", Toast.LENGTH_LONG).show()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        prefs = AppPreferences(this)
        dashboardCache = AgentDashboardCache(this)
        syncRepository = OfflineSyncRepository.get(this)

        setupHeader()
        requestPermissionsIfNeeded()
        setupListeners()
        setupAgentListeners()
        setupUpdatePanel()
        setupBackNavigation()

        lifecycleScope.launch {
            if (checkForAppUpdate()) return@launch
            initializeMainUi()
        }
    }

    private suspend fun initializeMainUi() {
        agentToken = prefs.agentSessionToken.first()
        ensureBoundAgentFromCache()
        binding.appHeader.visibility = View.VISIBLE
        binding.codeEntryPanel.visibility = View.GONE
        showAgentTab()
        if (!agentToken.isNullOrBlank()) {
            refreshAgentDashboard()
        }
    }

    private fun setupBackNavigation() {
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (binding.configPanel.visibility == View.VISIBLE) {
                    showAgentTab()
                } else {
                    isEnabled = false
                    onBackPressedDispatcher.onBackPressed()
                }
            }
        })
    }

    private fun setupHeader() {
        binding.buttonRefresh.setOnClickListener { refreshAgentDashboard() }
        binding.buttonSettings.setOnClickListener { showSettingsMenu(it) }
        binding.buttonConfigBack.setOnClickListener { showAgentTab() }
    }

    private fun showSettingsMenu(anchor: View) {
        val popup = PopupMenu(this, anchor)
        popup.menuInflater.inflate(R.menu.main_settings_menu, popup.menu)
        val loggedIn = !agentToken.isNullOrBlank()
        popup.menu.findItem(R.id.menu_change_password).isEnabled = loggedIn
        popup.menu.findItem(R.id.menu_logout).isEnabled = loggedIn
        popup.setOnMenuItemClickListener { item ->
            when (item.itemId) {
                R.id.menu_sms_service -> {
                    requestSmsConfigAccess()
                    true
                }
                R.id.menu_change_password -> {
                    if (loggedIn) showChangePasswordDialog()
                    else Toast.makeText(this, "Connectez-vous d'abord.", Toast.LENGTH_SHORT).show()
                    true
                }
                R.id.menu_faq -> {
                    openFaq()
                    true
                }
                R.id.menu_logout -> {
                    if (loggedIn) performAgentLogout()
                    else Toast.makeText(this, "Connectez-vous d'abord.", Toast.LENGTH_SHORT).show()
                    true
                }
                else -> false
            }
        }
        popup.show()
    }

    private fun openFaq() {
        lifecycleScope.launch {
            val base = prefs.apiBaseUrl.first()?.trimEnd('/')
            if (base.isNullOrBlank()) {
                Toast.makeText(this@MainActivity, R.string.agent_api_url_required, Toast.LENGTH_LONG).show()
                return@launch
            }
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse("$base/faq")))
        }
    }

    override fun onResume() {
        super.onResume()
        lifecycleScope.launch {
            if (binding.updateRequiredPanel.visibility == View.VISIBLE) {
                if (!checkForAppUpdate()) {
                    hideUpdateRequired()
                    initializeMainUi()
                }
                return@launch
            }
            checkForAppUpdate()
            updateQueueStatus(syncRepository.pendingTotalCount())
        }
    }

    private fun setupUpdatePanel() {
        binding.buttonDownloadUpdate.setOnClickListener {
            pendingUpdateResult?.let { AppUpdateChecker.openDownload(this, it) }
        }
        binding.buttonRetryUpdateCheck.setOnClickListener {
            lifecycleScope.launch { checkForAppUpdate() }
        }
    }

    /**
     * @return true si l'écran de mise à jour bloque l'app
     */
    private suspend fun checkForAppUpdate(): Boolean {
        val apiUrl = prefs.apiBaseUrl.first()
        if (apiUrl.isNullOrBlank()) {
            hideUpdateRequired()
            return false
        }
        if (!NetworkUtils.isOnline(this)) {
            return false
        }

        return try {
            val result = AppUpdateChecker.check(apiUrl)
            if (result.updateRequired) {
                showUpdateRequired(result)
                true
            } else {
                hideUpdateRequired()
                false
            }
        } catch (_: Exception) {
            hideUpdateRequired()
            false
        }
    }

    private fun showUpdateRequired(result: AppUpdateChecker.Result) {
        pendingUpdateResult = result
        binding.updateRequiredPanel.visibility = View.VISIBLE
        binding.appHeader.visibility = View.GONE
        binding.codeEntryPanel.visibility = View.GONE
        binding.configPanel.visibility = View.GONE
        binding.agentPanel.visibility = View.GONE
        binding.textUpdateMessage.text = getString(
            R.string.update_version_info,
            result.currentVersionName,
            result.currentVersionCode,
            result.serverVersionName ?: "?",
            result.serverVersionCode,
        )
    }

    private fun hideUpdateRequired() {
        binding.updateRequiredPanel.visibility = View.GONE
        pendingUpdateResult = null
        binding.appHeader.visibility = View.VISIBLE
    }

    private fun updateQueueStatus(pendingCount: Int) {
        if (!smsConfigUnlockedThisSession) return
        if (pendingCount > 0) {
            binding.textQueueStatus.visibility = View.VISIBLE
            binding.textQueueStatus.text = getString(R.string.queue_pending_status, pendingCount)
        } else {
            binding.textQueueStatus.visibility = View.GONE
            binding.textQueueStatus.text = getString(R.string.queue_empty_status)
        }
    }

    private fun requestSmsConfigAccess() {
        if (smsConfigUnlockedThisSession) {
            showSmsTab()
            return
        }
        lifecycleScope.launch {
            if (prefs.isApiConfigured()) {
                showConfigCodeDialog()
            } else {
                showFirstSetupDialog()
            }
        }
    }

    private fun showSmsTab() {
        binding.configPanel.visibility = View.VISIBLE
        binding.agentPanel.visibility = View.GONE
        binding.buttonRefresh.visibility = View.GONE
        lifecycleScope.launch {
            loadSettingsSync()
            updateQueueStatus(syncRepository.pendingTotalCount())
        }
    }

    private fun showAgentTab() {
        binding.configPanel.visibility = View.GONE
        binding.agentPanel.visibility = View.VISIBLE
        updateAgentUi()
    }

    private fun showConfigCodeDialog() {
        val view = layoutInflater.inflate(R.layout.dialog_sms_config_code, null)
        val editCode = view.findViewById<TextInputEditText>(R.id.edit_dialog_config_code)

        configAccessDialog?.dismiss()
        configAccessDialog = AlertDialog.Builder(this)
            .setTitle(R.string.dialog_sms_config_title)
            .setView(view)
            .setPositiveButton(R.string.code_access_button, null)
            .setNegativeButton(R.string.code_access_cancel, null)
            .create()

        configAccessDialog?.setOnShowListener {
            configAccessDialog?.getButton(AlertDialog.BUTTON_POSITIVE)?.setOnClickListener {
                lifecycleScope.launch {
                    val ok = validateConfigAccess(
                        apiUrl = prefs.apiBaseUrl.first(),
                        apiToken = prefs.apiToken.first(),
                        configCode = editCode.text?.toString()?.trim().orEmpty(),
                        configured = true,
                    )
                    if (ok) {
                        configAccessDialog?.dismiss()
                        unlockSmsConfig()
                    }
                }
            }
        }
        configAccessDialog?.show()
    }

    private suspend fun showFirstSetupDialog() {
        val view = layoutInflater.inflate(R.layout.dialog_sms_first_setup, null)
        val editUrl = view.findViewById<TextInputEditText>(R.id.edit_dialog_setup_url)
        val editToken = view.findViewById<TextInputEditText>(R.id.edit_dialog_setup_token)
        val editCode = view.findViewById<TextInputEditText>(R.id.edit_dialog_setup_code)
        editUrl.setText(prefs.apiBaseUrl.first() ?: "")
        editToken.setText(prefs.apiToken.first() ?: "")

        configAccessDialog?.dismiss()
        configAccessDialog = AlertDialog.Builder(this)
            .setTitle(R.string.dialog_first_setup_title)
            .setView(view)
            .setPositiveButton(R.string.code_access_button, null)
            .setNegativeButton(R.string.code_access_cancel, null)
            .create()

        configAccessDialog?.setOnShowListener {
            configAccessDialog?.getButton(AlertDialog.BUTTON_POSITIVE)?.setOnClickListener {
                lifecycleScope.launch {
                    val ok = validateConfigAccess(
                        apiUrl = editUrl.text?.toString()?.trim(),
                        apiToken = editToken.text?.toString()?.trim(),
                        configCode = editCode.text?.toString()?.trim().orEmpty(),
                        configured = false,
                    )
                    if (ok) {
                        configAccessDialog?.dismiss()
                        unlockSmsConfig()
                    }
                }
            }
        }
        configAccessDialog?.show()
    }

    private fun unlockSmsConfig() {
        smsConfigUnlockedThisSession = true
        showSmsTab()
    }

    private suspend fun validateConfigAccess(
        apiUrl: String?,
        apiToken: String?,
        configCode: String,
        configured: Boolean,
    ): Boolean {
        if (apiUrl.isNullOrBlank()) {
            Toast.makeText(this, R.string.api_url_required_for_verify, Toast.LENGTH_LONG).show()
            return false
        }

        if (!configured && apiToken.isNullOrBlank()) {
            Toast.makeText(this, R.string.api_setup_required, Toast.LENGTH_LONG).show()
            return false
        }

        if (configCode.isBlank()) {
            Toast.makeText(this, "Code d'accès requis.", Toast.LENGTH_SHORT).show()
            return false
        }

        if (!NetworkUtils.isOnline(this)) {
            Toast.makeText(this, R.string.agent_login_offline, Toast.LENGTH_LONG).show()
            return false
        }

        return try {
            val verify = MobileConfigApiClient.create(apiUrl).verifyConfigCode(VerifyConfigCodeRequest(configCode))
            if (!verify.valid) {
                Toast.makeText(this, verify.message ?: getString(R.string.code_config_invalid), Toast.LENGTH_LONG).show()
                false
            } else {
                if (!configured) {
                    prefs.setApiBaseUrl(apiUrl)
                    prefs.setApiToken(apiToken!!)
                    Toast.makeText(this, "URL et token enregistrés.", Toast.LENGTH_SHORT).show()
                }
                true
            }
        } catch (_: Exception) {
            Toast.makeText(this, R.string.code_config_invalid, Toast.LENGTH_LONG).show()
            false
        }
    }

    private fun requestPermissionsIfNeeded() {
        val needed = mutableListOf<String>()
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                needed.add(Manifest.permission.POST_NOTIFICATIONS)
            }
        }
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.RECEIVE_SMS) != PackageManager.PERMISSION_GRANTED) {
            needed.add(Manifest.permission.RECEIVE_SMS)
        }
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.READ_SMS) != PackageManager.PERMISSION_GRANTED) {
            needed.add(Manifest.permission.READ_SMS)
        }
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.READ_PHONE_STATE) != PackageManager.PERMISSION_GRANTED) {
            needed.add(Manifest.permission.READ_PHONE_STATE)
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O &&
            ContextCompat.checkSelfPermission(this, Manifest.permission.READ_PHONE_NUMBERS) != PackageManager.PERMISSION_GRANTED
        ) {
            needed.add(Manifest.permission.READ_PHONE_NUMBERS)
        }
        if (needed.isNotEmpty()) {
            requestPermissions.launch(needed.toTypedArray())
        }
    }

    private suspend fun loadSettingsSync() {
        binding.switchServiceEnabled.isChecked = prefs.serviceEnabled.first()
        binding.editApiUrl.setText(prefs.apiBaseUrl.first() ?: "")
        binding.editApiToken.setText(prefs.apiToken.first() ?: "")
    }

    private fun setupListeners() {
        binding.buttonSave.setOnClickListener { saveAndMaybeStartService() }
        binding.buttonSyncNow.setOnClickListener { triggerManualSync() }
    }

    private fun setupAgentListeners() {
        binding.buttonAgentLogin.setOnClickListener { performAgentLogin() }
    }

    private fun triggerManualSync() {
        lifecycleScope.launch {
            binding.buttonSyncNow.isEnabled = false
            val result = syncRepository.syncAll()
            binding.buttonSyncNow.isEnabled = true
            updateQueueStatus(result.stillPending)
            if (result.transactionsSynced + result.actionsSynced > 0) {
                refreshAgentDashboard()
            }
            val message = when {
                result.stillPending == 0 -> "Synchronisation terminée."
                result.networkError && !NetworkUtils.isOnline(this@MainActivity) ->
                    "Pas de réseau — ${result.stillPending} opération(s) en attente."
                result.networkError ->
                    "Serveur inaccessible — ${result.stillPending} opération(s) en attente."
                else -> "${result.stillPending} opération(s) restante(s)."
            }
            Toast.makeText(this@MainActivity, message, Toast.LENGTH_SHORT).show()
        }
    }

    private fun saveAndMaybeStartService() {
        val apiUrl = binding.editApiUrl.text?.toString()?.trim() ?: ""
        val apiToken = binding.editApiToken.text?.toString()?.trim() ?: ""
        val serviceEnabled = binding.switchServiceEnabled.isChecked

        if (serviceEnabled && (apiUrl.isBlank() || apiToken.isBlank())) {
            Toast.makeText(this, "URL de l'API et Token sont requis pour activer le service.", Toast.LENGTH_LONG).show()
            return
        }

        lifecycleScope.launch {
            prefs.saveAll(consent = true, serviceEnabled, apiUrl, apiToken, filterList = emptyList())
            if (serviceEnabled) {
                startForegroundServiceIfNeeded()
                Toast.makeText(this@MainActivity, "Paramètres enregistrés. Le transfert SMS est actif.", Toast.LENGTH_SHORT).show()
            } else {
                stopService(Intent(this@MainActivity, SmsForwarderService::class.java))
                Toast.makeText(this@MainActivity, "Paramètres enregistrés. Service désactivé.", Toast.LENGTH_SHORT).show()
            }
            if (NetworkUtils.isOnline(this@MainActivity)) {
                SyncScheduler.scheduleImmediate(this@MainActivity)
            }
        }
    }

    private fun performAgentLogin() {
        lifecycleScope.launch {
            if (!NetworkUtils.isOnline(this@MainActivity)) {
                Toast.makeText(this@MainActivity, R.string.agent_login_offline, Toast.LENGTH_LONG).show()
                return@launch
            }

            val apiUrl = prefs.apiBaseUrl.first()
            if (apiUrl.isNullOrBlank()) {
                Toast.makeText(this@MainActivity, R.string.agent_api_url_required, Toast.LENGTH_LONG).show()
                return@launch
            }

            val identifiant = binding.editAgentIdentifiant.text?.toString()?.trim() ?: ""
            val password = binding.editAgentPassword.text?.toString() ?: ""

            if (identifiant.isBlank() || password.isBlank()) {
                Toast.makeText(this@MainActivity, "Code agent et mot de passe requis.", Toast.LENGTH_SHORT).show()
                return@launch
            }

            try {
                val api = AgentApiClient.create(apiUrl)
                val response = api.login(AgentLoginRequest(identifiant, password))
                if (response.success && !response.token.isNullOrBlank()) {
                    agentToken = response.token
                    currentAgent = response.agent
                    prefs.setAgentSessionToken(response.token)
                    response.agent?.let { agent ->
                        prefs.setBoundAgent(agent.id, agent.codeAgent, agent.telephone)
                    }
                    dashboardCache.save(response)
                    showingOfflineCache = false
                    renderDashboard(response.dashboard, response.agent, offline = false)
                    Toast.makeText(this@MainActivity, "Connexion réussie.", Toast.LENGTH_SHORT).show()
                } else {
                    Toast.makeText(this@MainActivity, response.message ?: "Connexion échouée.", Toast.LENGTH_LONG).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Erreur réseau : ${e.message}", Toast.LENGTH_LONG).show()
            }
        }
    }

    private fun refreshAgentDashboard() {
        lifecycleScope.launch {
            val apiUrl = prefs.apiBaseUrl.first()
            val token = agentToken ?: prefs.agentSessionToken.first()
            if (apiUrl.isNullOrBlank() || token.isNullOrBlank()) {
                updateAgentUi()
                return@launch
            }

            if (!NetworkUtils.isOnline(this@MainActivity)) {
                loadCachedDashboard(serverUnreachable = false)
                return@launch
            }

            try {
                val api = AgentApiClient.create(apiUrl)
                val response = api.dashboard("Bearer $token")
                if (response.success) {
                    currentAgent = response.agent ?: currentAgent
                    currentAgent?.let { agent ->
                        prefs.setBoundAgent(agent.id, agent.codeAgent, agent.telephone)
                    }
                    dashboardCache.save(
                        AgentLoginResponse(
                            success = true,
                            agent = currentAgent,
                            dashboard = response.dashboard,
                        ),
                    )
                    showingOfflineCache = false
                    showingServerUnreachable = false
                    renderDashboard(response.dashboard, currentAgent, offline = false)
                } else {
                    prefs.setAgentSessionToken(null)
                    agentToken = null
                    dashboardCache.clear()
                    updateAgentUi()
                }
            } catch (e: IOException) {
                loadCachedDashboard(serverUnreachable = true)
            } catch (_: Exception) {
                loadCachedDashboard(serverUnreachable = true)
            }
        }
    }

    private suspend fun loadCachedDashboard(serverUnreachable: Boolean) {
        val cached = dashboardCache.load()
        val trulyOffline = !NetworkUtils.isOnline(this)
        if (cached != null) {
            currentAgent = cached.agent ?: currentAgent
            showingOfflineCache = trulyOffline
            showingServerUnreachable = serverUnreachable && !trulyOffline
            renderDashboard(
                cached.dashboard,
                currentAgent,
                offline = trulyOffline,
                serverUnreachable = showingServerUnreachable,
            )
        } else {
            binding.textAgentOfflineBanner.visibility = View.VISIBLE
            binding.textAgentOfflineBanner.text = if (trulyOffline) {
                getString(R.string.agent_login_offline)
            } else {
                getString(R.string.agent_server_unreachable_banner)
            }
        }
    }

    private fun performAgentLogout() {
        lifecycleScope.launch {
            val apiUrl = prefs.apiBaseUrl.first()
            val token = agentToken
            if (!apiUrl.isNullOrBlank() && !token.isNullOrBlank() && NetworkUtils.isOnline(this@MainActivity)) {
                try {
                    AgentApiClient.create(apiUrl).logout("Bearer $token")
                } catch (_: Exception) {
                }
            }
            agentToken = null
            currentAgent = null
            showingOfflineCache = false
            showingServerUnreachable = false
            prefs.setAgentSessionToken(null)
            prefs.clearBoundAgent()
            dashboardCache.clear()
            binding.editAgentPassword.setText("")
            binding.textAgentOfflineBanner.visibility = View.GONE
            showAgentTab()
            updateAgentUi()
        }
    }

    private suspend fun ensureBoundAgentFromCache() {
        if (prefs.boundAgentId.first() != null) return
        dashboardCache.load()?.agent?.let { agent ->
            prefs.setBoundAgent(agent.id, agent.codeAgent, agent.telephone)
        }
    }

    private fun updateAgentUi() {
        val loggedIn = !agentToken.isNullOrBlank()
        binding.agentLoginSection.visibility = if (loggedIn) View.GONE else View.VISIBLE
        binding.agentDashboardSection.visibility = if (loggedIn) View.VISIBLE else View.GONE
        val showRefresh = loggedIn && binding.agentPanel.visibility == View.VISIBLE
        binding.buttonRefresh.visibility = if (showRefresh) View.VISIBLE else View.GONE
    }

    private fun renderDashboard(
        dashboard: AgentDashboard?,
        agent: AgentInfo?,
        offline: Boolean,
        serverUnreachable: Boolean = false,
    ) {
        updateAgentUi()
        val showBanner = offline || serverUnreachable
        binding.textAgentOfflineBanner.visibility = if (showBanner) View.VISIBLE else View.GONE
        binding.textAgentOfflineBanner.text = when {
            serverUnreachable -> getString(R.string.agent_server_unreachable_banner)
            offline -> getString(R.string.agent_offline_banner)
            else -> ""
        }

        val readOnly = offline || serverUnreachable

        agent?.let {
            binding.textAgentWelcome.text = "Bonjour ${it.prenom ?: ""} ${it.nom ?: ""}".trim()
        }

        val fmt = NumberFormat.getNumberInstance(Locale.FRANCE)
        renderBalances(dashboard?.balances, fmt)

        val stats = dashboard?.stats

        binding.agentTodayOperateurCards.removeAllViews()
        binding.agentMonthOperateurCards.removeAllViews()
        binding.agentTodaySummaryCards.removeAllViews()
        binding.agentMonthSummaryCards.removeAllViews()

        stats?.todayByOperateur.orEmpty().forEach { op ->
            binding.agentTodayOperateurCards.addView(createOperateurCard(op, fmt))
        }
        stats?.monthByOperateur.orEmpty().forEach { op ->
            binding.agentMonthOperateurCards.addView(createOperateurCard(op, fmt))
        }

        binding.agentTodaySummaryCards.addView(
            createSummaryCard(
                getString(R.string.agent_stats_ca),
                "${fmt.format(stats?.todayTotal ?: 0.0)} F",
                getString(R.string.agent_stats_tx_count, stats?.todayCount ?: 0),
                R.color.primary,
            ),
        )
        binding.agentTodaySummaryCards.addView(
            createSummaryCard(
                getString(R.string.agent_stats_commission),
                "${fmt.format(stats?.todayCommission ?: 0.0)} F",
                getString(R.string.agent_stats_today_title),
                R.color.flooz_blue,
            ),
        )

        binding.agentMonthSummaryCards.addView(
            createSummaryCard(
                getString(R.string.agent_stats_ca),
                "${fmt.format(stats?.monthTotal ?: 0.0)} F",
                getString(R.string.agent_stats_tx_count, stats?.monthCount ?: 0),
                R.color.primary,
            ),
        )
        binding.agentMonthSummaryCards.addView(
            createSummaryCard(
                getString(R.string.agent_stats_commission),
                "${fmt.format(stats?.monthCommission ?: 0.0)} F",
                getString(R.string.agent_stats_month_title),
                R.color.flooz_blue,
            ),
        )

        binding.agentTransactionsList.removeAllViews()
        val transactions = dashboard?.transactions.orEmpty()
        if (transactions.isEmpty()) {
            binding.agentTransactionsList.addView(TextView(this).apply {
                text = "Aucune transaction ce mois."
                setPadding(0, 16, 0, 16)
            })
            return
        }

        transactions.forEach { tx ->
            binding.agentTransactionsList.addView(createTransactionRow(tx, readOnly))
        }
    }

    private fun renderBalances(balances: AgentBalances?, fmt: NumberFormat) {
        binding.agentBalanceCards.removeAllViews()
        if (balances == null) return

        binding.agentBalanceCards.addView(
            createBalanceCard(getString(R.string.agent_balance_espece), balances.espece, fmt, R.color.primary),
        )
        balances.virtuels.orEmpty().forEach { virtuel ->
            val accent = when (virtuel.code?.uppercase()) {
                "YAS" -> R.color.yas_brown
                "FLOOZ" -> R.color.flooz_blue
                else -> R.color.primary
            }
            val label = getString(
                R.string.agent_balance_virtuel,
                virtuel.libelle ?: virtuel.code ?: "Opérateur",
            )
            binding.agentBalanceCards.addView(createBalanceCard(label, virtuel.montant, fmt, accent))
        }
    }

    private fun createBalanceCard(title: String, amount: Double, fmt: NumberFormat, accentColor: Int): MaterialCardView {
        val padding = dp(12)
        val inner = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(padding, padding, padding, padding)
        }
        inner.addView(TextView(this).apply {
            text = title
            textSize = 13f
            setTextColor(ContextCompat.getColor(this@MainActivity, accentColor))
            setTypeface(typeface, android.graphics.Typeface.BOLD)
        })
        inner.addView(TextView(this).apply {
            text = "${fmt.format(amount)} F"
            textSize = 20f
            setPadding(0, dp(4), 0, 0)
            setTypeface(typeface, android.graphics.Typeface.BOLD)
        })

        return MaterialCardView(this).apply {
            layoutParams = LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT,
            ).apply { bottomMargin = dp(8) }
            radius = dp(12).toFloat()
            cardElevation = dp(2).toFloat()
            setCardBackgroundColor(ContextCompat.getColor(this@MainActivity, R.color.card_bg))
            addView(inner)
        }
    }

    private fun showChangePasswordDialog() {
        val token = agentToken
        if (token.isNullOrBlank()) {
            Toast.makeText(this, "Connectez-vous d'abord.", Toast.LENGTH_SHORT).show()
            return
        }

        val view = layoutInflater.inflate(R.layout.dialog_change_password, null)
        val editCurrent = view.findViewById<TextInputEditText>(R.id.edit_current_password)
        val editNew = view.findViewById<TextInputEditText>(R.id.edit_new_password)
        val editConfirm = view.findViewById<TextInputEditText>(R.id.edit_confirm_password)

        val dialog = AlertDialog.Builder(this)
            .setTitle(R.string.dialog_change_password_title)
            .setView(view)
            .setPositiveButton(R.string.save, null)
            .setNegativeButton(android.R.string.cancel, null)
            .create()

        dialog.setOnShowListener {
            dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener {
                val current = editCurrent.text?.toString().orEmpty()
                val newPass = editNew.text?.toString().orEmpty()
                val confirm = editConfirm.text?.toString().orEmpty()

                if (current.isBlank() || newPass.isBlank()) {
                    Toast.makeText(this, "Tous les champs sont requis.", Toast.LENGTH_SHORT).show()
                    return@setOnClickListener
                }
                if (newPass.length < 8) {
                    Toast.makeText(this, "Minimum 8 caractères.", Toast.LENGTH_SHORT).show()
                    return@setOnClickListener
                }
                if (newPass != confirm) {
                    Toast.makeText(this, R.string.agent_password_mismatch, Toast.LENGTH_SHORT).show()
                    return@setOnClickListener
                }

                lifecycleScope.launch {
                    if (!NetworkUtils.isOnline(this@MainActivity)) {
                        Toast.makeText(this@MainActivity, R.string.agent_login_offline, Toast.LENGTH_LONG).show()
                        return@launch
                    }
                    val apiUrl = prefs.apiBaseUrl.first()
                    if (apiUrl.isNullOrBlank()) {
                        Toast.makeText(this@MainActivity, R.string.agent_api_url_required, Toast.LENGTH_LONG).show()
                        return@launch
                    }
                    try {
                        val response = AgentApiClient.create(apiUrl).changePassword(
                            "Bearer $token",
                            AgentChangePasswordRequest(current, newPass, confirm),
                        )
                        if (response.success) {
                            dialog.dismiss()
                            Toast.makeText(this@MainActivity, R.string.agent_password_changed, Toast.LENGTH_SHORT).show()
                        } else {
                            Toast.makeText(
                                this@MainActivity,
                                response.message ?: "Échec du changement.",
                                Toast.LENGTH_LONG,
                            ).show()
                        }
                    } catch (e: Exception) {
                        Toast.makeText(this@MainActivity, "Erreur : ${e.message}", Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
        dialog.show()
    }

    private fun createOperateurCard(op: OperateurStats, fmt: NumberFormat): MaterialCardView {
        val accent = when (op.code?.uppercase()) {
            "YAS" -> R.color.yas_brown
            "FLOOZ" -> R.color.flooz_blue
            else -> R.color.primary
        }
        val padding = dp(12)
        val inner = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(padding, padding, padding, padding)
        }
        inner.addView(TextView(this).apply {
            text = op.libelle ?: op.code ?: "Opérateur"
            textSize = 14f
            setTypeface(typeface, android.graphics.Typeface.BOLD)
            setTextColor(ContextCompat.getColor(this@MainActivity, accent))
        })
        inner.addView(TextView(this).apply {
            text = "${fmt.format(op.total)} F · ${getString(R.string.agent_stats_tx_count, op.count)}"
            textSize = 15f
            setPadding(0, dp(4), 0, 0)
        })
        inner.addView(TextView(this).apply {
            text = "${getString(R.string.agent_stats_commission)} : ${fmt.format(op.commission)} F"
            textSize = 13f
            setTextColor(ContextCompat.getColor(this@MainActivity, R.color.text_muted))
            setPadding(0, dp(2), 0, 0)
        })

        return MaterialCardView(this).apply {
            layoutParams = LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT,
            ).apply { bottomMargin = dp(8) }
            radius = dp(12).toFloat()
            cardElevation = dp(2).toFloat()
            setCardBackgroundColor(ContextCompat.getColor(this@MainActivity, R.color.card_bg))
            addView(inner)
        }
    }

    private fun createSummaryCard(
        label: String,
        value: String,
        subtitle: String,
        accentColorRes: Int,
    ): MaterialCardView {
        val padding = dp(12)
        val inner = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(padding, padding, padding, padding)
            gravity = Gravity.CENTER_VERTICAL
        }
        inner.addView(TextView(this).apply {
            text = label
            textSize = 12f
            setTextColor(ContextCompat.getColor(this@MainActivity, R.color.text_muted))
        })
        inner.addView(TextView(this).apply {
            text = value
            textSize = 16f
            setTypeface(typeface, android.graphics.Typeface.BOLD)
            setTextColor(ContextCompat.getColor(this@MainActivity, accentColorRes))
            setPadding(0, dp(4), 0, 0)
        })
        inner.addView(TextView(this).apply {
            text = subtitle
            textSize = 11f
            setTextColor(ContextCompat.getColor(this@MainActivity, R.color.text_muted))
            setPadding(0, dp(2), 0, 0)
        })

        return MaterialCardView(this).apply {
            layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f).apply {
                marginEnd = dp(6)
            }
            radius = dp(12).toFloat()
            cardElevation = dp(2).toFloat()
            setCardBackgroundColor(ContextCompat.getColor(this@MainActivity, R.color.card_bg))
            addView(inner)
        }
    }

    private fun dp(value: Int): Int = TypedValue.applyDimension(
        TypedValue.COMPLEX_UNIT_DIP,
        value.toFloat(),
        resources.displayMetrics,
    ).toInt()

    private fun createTransactionRow(tx: AgentTransaction, offline: Boolean): View {
        val fmt = NumberFormat.getNumberInstance(Locale.FRANCE)
        val padding = dp(12)
        val inner = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(padding, padding, padding, padding)
        }

        val accent = when (tx.operateurCode?.uppercase()) {
            "YAS" -> R.color.yas_brown
            "FLOOZ" -> R.color.flooz_blue
            else -> R.color.primary
        }

        inner.addView(TextView(this).apply {
            text = tx.reference ?: "—"
            textSize = 14f
            setTypeface(typeface, android.graphics.Typeface.BOLD)
        })
        inner.addView(TextView(this).apply {
            text = buildString {
                append((tx.type ?: "").replaceFirstChar { it.uppercase() })
                append(" · ")
                append(fmt.format(tx.montant))
                append(" F")
            }
            textSize = 16f
            setTextColor(ContextCompat.getColor(this@MainActivity, accent))
            setPadding(0, dp(4), 0, 0)
        })
        inner.addView(TextView(this).apply {
            text = "${tx.operateur ?: "N/A"} · ${tx.date ?: ""} · ${tx.statut ?: ""}"
            textSize = 13f
            setTextColor(ContextCompat.getColor(this@MainActivity, R.color.text_muted))
            setPadding(0, dp(2), 0, 0)
        })
        inner.addView(TextView(this).apply {
            text = "${getString(R.string.agent_stats_commission)} : ${fmt.format(tx.commission)} F"
            textSize = 13f
            setPadding(0, dp(2), 0, 0)
        })

        if (tx.canCancel && !offline) {
            inner.addView(Button(this, null, com.google.android.material.R.attr.materialButtonOutlinedStyle).apply {
                text = getString(R.string.agent_cancel)
                layoutParams = LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.WRAP_CONTENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT,
                ).apply { topMargin = dp(8) }
                setOnClickListener { confirmCancelTransaction(tx) }
            })
        }

        return MaterialCardView(this).apply {
            layoutParams = LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT,
            ).apply { bottomMargin = dp(10) }
            radius = dp(10).toFloat()
            cardElevation = dp(1).toFloat()
            setCardBackgroundColor(ContextCompat.getColor(this@MainActivity, R.color.white))
            addView(inner)
        }
    }

    private fun confirmCancelTransaction(tx: AgentTransaction) {
        val input = android.widget.EditText(this).apply {
            hint = "Raison de l'annulation"
        }
        AlertDialog.Builder(this)
            .setTitle("Annuler ${tx.reference}")
            .setMessage(getString(R.string.agent_cancel_dialog_message))
            .setView(input)
            .setPositiveButton("Annuler la transaction") { _, _ ->
                val raison = input.text?.toString()?.trim().orEmpty()
                if (raison.isBlank()) {
                    Toast.makeText(this, "La raison est obligatoire.", Toast.LENGTH_SHORT).show()
                    return@setPositiveButton
                }
                lifecycleScope.launch { cancelTransaction(tx.id, raison) }
            }
            .setNegativeButton("Fermer", null)
            .show()
    }

    private suspend fun cancelTransaction(id: Long, raison: String) {
        val apiUrl = prefs.apiBaseUrl.first()
        val token = agentToken ?: prefs.agentSessionToken.first()
        if (apiUrl.isNullOrBlank() || token.isNullOrBlank()) return

        if (!NetworkUtils.isOnline(this)) {
            syncRepository.enqueueCancelTransaction(id, raison, token, apiUrl)
            Toast.makeText(this, R.string.agent_cancel_queued, Toast.LENGTH_LONG).show()
            updateQueueStatus(syncRepository.pendingTotalCount())
            return
        }

        try {
            val response = AgentApiClient.create(apiUrl)
                .cancelTransaction("Bearer $token", id, AgentCancelRequest(raison))
            if (response.success) {
                Toast.makeText(this, response.message ?: "Transaction annulée.", Toast.LENGTH_SHORT).show()
                dashboardCache.save(
                    AgentLoginResponse(
                        success = true,
                        agent = response.agent ?: currentAgent,
                        dashboard = response.dashboard,
                    ),
                )
                renderDashboard(response.dashboard, response.agent ?: currentAgent, offline = false)
            } else {
                Toast.makeText(this, response.message ?: "Échec de l'annulation.", Toast.LENGTH_LONG).show()
            }
        } catch (e: IOException) {
            syncRepository.enqueueCancelTransaction(id, raison, token, apiUrl)
            Toast.makeText(this, R.string.agent_cancel_queued, Toast.LENGTH_LONG).show()
            updateQueueStatus(syncRepository.pendingTotalCount())
        } catch (e: Exception) {
            Toast.makeText(this, "Erreur : ${e.message}", Toast.LENGTH_LONG).show()
        }
    }

    private fun startForegroundServiceIfNeeded() {
        ServiceStarter.startForegroundService(this)
    }
}
