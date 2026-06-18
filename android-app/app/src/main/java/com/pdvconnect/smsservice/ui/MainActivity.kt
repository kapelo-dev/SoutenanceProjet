package com.pdvconnect.smsservice.ui

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.util.TypedValue
import android.view.Gravity
import android.view.View
import android.widget.Button
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.lifecycle.lifecycleScope
import com.google.android.material.card.MaterialCardView
import com.google.android.material.tabs.TabLayout
import com.pdvconnect.smsservice.R
import com.pdvconnect.smsservice.api.AgentApiClient
import com.pdvconnect.smsservice.api.AgentCancelRequest
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
    private var suppressTabCallback = false
    private var agentToken: String? = null
    private var currentAgent: AgentInfo? = null
    private var showingOfflineCache = false
    private var pendingUpdateResult: AppUpdateChecker.Result? = null

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

        setupTabs()
        requestPermissionsIfNeeded()
        setupCodeAccessOverlay()
        setupListeners()
        setupAgentListeners()
        setupUpdatePanel()

        lifecycleScope.launch {
            if (checkForAppUpdate()) return@launch
            initializeMainUi()
        }
    }

    private suspend fun initializeMainUi() {
        agentToken = prefs.agentSessionToken.first()
        binding.mainTabs.visibility = View.VISIBLE
        binding.codeEntryPanel.visibility = View.GONE
        suppressTabCallback = true
        binding.mainTabs.getTabAt(TAB_AGENT)?.select()
        suppressTabCallback = false
        showAgentTab()
        if (!agentToken.isNullOrBlank()) {
            refreshAgentDashboard()
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
            return binding.updateRequiredPanel.visibility == View.VISIBLE
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
            return binding.updateRequiredPanel.visibility == View.VISIBLE
        }
    }

    private fun showUpdateRequired(result: AppUpdateChecker.Result) {
        pendingUpdateResult = result
        binding.updateRequiredPanel.visibility = View.VISIBLE
        binding.mainTabs.visibility = View.GONE
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
        binding.mainTabs.visibility = View.VISIBLE
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

    private fun setupTabs() {
        binding.mainTabs.addTab(binding.mainTabs.newTab().setText(R.string.tab_sms))
        binding.mainTabs.addTab(binding.mainTabs.newTab().setText(R.string.tab_agent))
        binding.mainTabs.addOnTabSelectedListener(object : TabLayout.OnTabSelectedListener {
            override fun onTabSelected(tab: TabLayout.Tab?) {
                if (suppressTabCallback) return
                if (tab?.position == TAB_SMS) {
                    requestSmsTabAccess()
                } else {
                    hideConfigAccessOverlay()
                    showAgentTab()
                }
            }
            override fun onTabUnselected(tab: TabLayout.Tab?) {}
            override fun onTabReselected(tab: TabLayout.Tab?) {
                if (tab?.position == TAB_SMS) requestSmsTabAccess()
            }
        })
    }

    private fun requestSmsTabAccess() {
        if (smsConfigUnlockedThisSession) {
            showSmsTab()
            return
        }
        lifecycleScope.launch {
            suppressTabCallback = true
            binding.mainTabs.getTabAt(TAB_AGENT)?.select()
            suppressTabCallback = false
            prepareConfigAccessOverlay()
            showConfigAccessOverlay()
        }
    }

    private fun showSmsTab() {
        hideConfigAccessOverlay()
        binding.configPanel.visibility = View.VISIBLE
        binding.agentPanel.visibility = View.GONE
        lifecycleScope.launch {
            loadSettingsSync()
            updateQueueStatus(syncRepository.pendingTotalCount())
        }
    }

    private fun showAgentTab() {
        hideConfigAccessOverlay()
        binding.configPanel.visibility = View.GONE
        binding.agentPanel.visibility = View.VISIBLE
        updateAgentUi()
    }

    private fun showConfigAccessOverlay() {
        binding.codeEntryPanel.visibility = View.VISIBLE
        binding.configPanel.visibility = View.GONE
        binding.agentPanel.visibility = View.GONE
    }

    private fun hideConfigAccessOverlay() {
        binding.codeEntryPanel.visibility = View.GONE
    }

    private suspend fun prepareConfigAccessOverlay() {
        val configured = prefs.isApiConfigured()

        binding.textCodeEntrySubtitle.text = if (configured) {
            getString(R.string.code_entry_subtitle)
        } else {
            getString(R.string.code_entry_setup_subtitle)
        }
        binding.layoutCodeEntryUrl.visibility = if (configured) View.GONE else View.VISIBLE
        binding.layoutCodeEntryToken.visibility = if (configured) View.GONE else View.VISIBLE
        binding.editCodeEntryUrl.setText(prefs.apiBaseUrl.first() ?: "")
        binding.editCodeEntryToken.setText(prefs.apiToken.first() ?: "")
        binding.editConfigCode.setText("")
    }

    private fun setupCodeAccessOverlay() {
        binding.buttonCodeCancel.setOnClickListener {
            hideConfigAccessOverlay()
            suppressTabCallback = true
            binding.mainTabs.getTabAt(TAB_AGENT)?.select()
            suppressTabCallback = false
            showAgentTab()
        }

        binding.buttonCodeAccess.setOnClickListener {
            lifecycleScope.launch { validateConfigAccess() }
        }
    }

    private suspend fun validateConfigAccess() {
        val configured = prefs.isApiConfigured()
        val apiUrl = binding.editCodeEntryUrl.text?.toString()?.trim()
            .takeUnless { it.isNullOrBlank() }
            ?: prefs.apiBaseUrl.first()
        val apiToken = binding.editCodeEntryToken.text?.toString()?.trim()
            .takeUnless { it.isNullOrBlank() }
            ?: prefs.apiToken.first()
        val configCode = binding.editConfigCode.text?.toString()?.trim() ?: ""

        if (apiUrl.isNullOrBlank()) {
            Toast.makeText(this, R.string.api_url_required_for_verify, Toast.LENGTH_LONG).show()
            return
        }

        if (!configured && apiToken.isNullOrBlank()) {
            Toast.makeText(this, R.string.api_setup_required, Toast.LENGTH_LONG).show()
            return
        }

        if (configCode.isBlank()) {
            Toast.makeText(this, "Code d'accès requis.", Toast.LENGTH_SHORT).show()
            return
        }

        if (!NetworkUtils.isOnline(this)) {
            Toast.makeText(this, R.string.agent_login_offline, Toast.LENGTH_LONG).show()
            return
        }

        try {
            val verify = MobileConfigApiClient.create(apiUrl).verifyConfigCode(VerifyConfigCodeRequest(configCode))
            if (!verify.valid) {
                Toast.makeText(this, verify.message ?: getString(R.string.code_config_invalid), Toast.LENGTH_LONG).show()
                return
            }
        } catch (_: Exception) {
            Toast.makeText(this, R.string.code_config_invalid, Toast.LENGTH_LONG).show()
            return
        }

        if (!configured) {
            prefs.setApiBaseUrl(apiUrl)
            prefs.setApiToken(apiToken!!)
            Toast.makeText(this, "URL et token enregistrés.", Toast.LENGTH_SHORT).show()
        }

        smsConfigUnlockedThisSession = true
        hideConfigAccessOverlay()
        suppressTabCallback = true
        binding.mainTabs.getTabAt(TAB_SMS)?.select()
        suppressTabCallback = false
        showSmsTab()
    }

    private fun requestPermissionsIfNeeded() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                requestPermissions.launch(arrayOf(Manifest.permission.POST_NOTIFICATIONS))
            }
        }
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.RECEIVE_SMS) != PackageManager.PERMISSION_GRANTED ||
            ContextCompat.checkSelfPermission(this, Manifest.permission.READ_SMS) != PackageManager.PERMISSION_GRANTED) {
            requestPermissions.launch(arrayOf(Manifest.permission.RECEIVE_SMS, Manifest.permission.READ_SMS))
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
        binding.buttonAgentRefresh.setOnClickListener { refreshAgentDashboard() }
        binding.buttonAgentLogout.setOnClickListener { performAgentLogout() }
    }

    private fun triggerManualSync() {
        lifecycleScope.launch {
            if (!NetworkUtils.isOnline(this@MainActivity)) {
                Toast.makeText(this@MainActivity, "Pas de connexion internet.", Toast.LENGTH_SHORT).show()
                return@launch
            }
            binding.buttonSyncNow.isEnabled = false
            val result = syncRepository.syncAll()
            binding.buttonSyncNow.isEnabled = true
            updateQueueStatus(result.stillPending)
            if (result.transactionsSynced + result.actionsSynced > 0) {
                refreshAgentDashboard()
            }
            Toast.makeText(
                this@MainActivity,
                if (result.stillPending == 0) "Synchronisation terminée." else "${result.stillPending} opération(s) restante(s).",
                Toast.LENGTH_SHORT,
            ).show()
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
                loadCachedDashboard()
                return@launch
            }

            try {
                val api = AgentApiClient.create(apiUrl)
                val response = api.dashboard("Bearer $token")
                if (response.success) {
                    currentAgent = response.agent ?: currentAgent
                    dashboardCache.save(
                        AgentLoginResponse(
                            success = true,
                            agent = currentAgent,
                            dashboard = response.dashboard,
                        ),
                    )
                    showingOfflineCache = false
                    renderDashboard(response.dashboard, currentAgent, offline = false)
                } else {
                    prefs.setAgentSessionToken(null)
                    agentToken = null
                    dashboardCache.clear()
                    updateAgentUi()
                }
            } catch (e: IOException) {
                loadCachedDashboard()
            } catch (_: Exception) {
                loadCachedDashboard()
            }
        }
    }

    private suspend fun loadCachedDashboard() {
        val cached = dashboardCache.load()
        if (cached != null) {
            currentAgent = cached.agent ?: currentAgent
            showingOfflineCache = true
            renderDashboard(cached.dashboard, currentAgent, offline = true)
        } else {
            binding.textAgentOfflineBanner.visibility = View.VISIBLE
            binding.textAgentOfflineBanner.text = getString(R.string.agent_login_offline)
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
            prefs.setAgentSessionToken(null)
            dashboardCache.clear()
            binding.editAgentPassword.setText("")
            binding.textAgentOfflineBanner.visibility = View.GONE
            updateAgentUi()
        }
    }

    private fun updateAgentUi() {
        val loggedIn = !agentToken.isNullOrBlank()
        binding.agentLoginSection.visibility = if (loggedIn) View.GONE else View.VISIBLE
        binding.agentDashboardSection.visibility = if (loggedIn) View.VISIBLE else View.GONE
    }

    private fun renderDashboard(dashboard: AgentDashboard?, agent: AgentInfo?, offline: Boolean) {
        updateAgentUi()
        binding.textAgentOfflineBanner.visibility = if (offline) View.VISIBLE else View.GONE

        agent?.let {
            binding.textAgentWelcome.text = "Bonjour ${it.prenom ?: ""} ${it.nom ?: ""}".trim()
        }

        val stats = dashboard?.stats
        val fmt = NumberFormat.getNumberInstance(Locale.FRANCE)

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
            binding.agentTransactionsList.addView(createTransactionRow(tx, offline))
        }
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

    companion object {
        private const val TAB_SMS = 0
        private const val TAB_AGENT = 1
    }
}
