package com.pdvconnect.smsservice.ui

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
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
import com.google.android.material.tabs.TabLayout
import com.pdvconnect.smsservice.R
import com.pdvconnect.smsservice.api.AgentApiClient
import com.pdvconnect.smsservice.api.AgentCancelRequest
import com.pdvconnect.smsservice.api.AgentDashboard
import com.pdvconnect.smsservice.api.AgentInfo
import com.pdvconnect.smsservice.api.AgentLoginRequest
import com.pdvconnect.smsservice.api.AgentLoginResponse
import com.pdvconnect.smsservice.api.AgentTransaction
import com.pdvconnect.smsservice.data.AgentDashboardCache
import com.pdvconnect.smsservice.data.AppPreferences
import com.pdvconnect.smsservice.databinding.ActivityMainBinding
import com.pdvconnect.smsservice.sms.ServiceStarter
import com.pdvconnect.smsservice.sms.SmsForwarderService
import com.pdvconnect.smsservice.sync.OfflineSyncRepository
import com.pdvconnect.smsservice.sync.SyncScheduler
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

    private var unlockedThisSession = false
    private var agentToken: String? = null
    private var currentAgent: AgentInfo? = null
    private var showingOfflineCache = false

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

        lifecycleScope.launch {
            agentToken = prefs.agentSessionToken.first()
            val code = prefs.configAccessCode.first()
            if (!code.isNullOrBlank() && !unlockedThisSession) {
                binding.codeEntryPanel.visibility = View.VISIBLE
                binding.configPanel.visibility = View.GONE
                binding.agentPanel.visibility = View.GONE
                binding.mainTabs.visibility = View.GONE
                setupCodeEntry()
            } else {
                binding.codeEntryPanel.visibility = View.GONE
                binding.mainTabs.visibility = View.VISIBLE
                showSmsTab()
                loadSettingsSync()
                setupListeners()
                setupAgentListeners()
                updateQueueStatus(syncRepository.pendingTotalCount())
                if (!agentToken.isNullOrBlank()) {
                    refreshAgentDashboard()
                }
            }
        }
    }

    override fun onResume() {
        super.onResume()
        lifecycleScope.launch {
            updateQueueStatus(syncRepository.pendingTotalCount())
        }
    }

    private fun updateQueueStatus(pendingCount: Int) {
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
                if (tab?.position == 1) showAgentTab() else showSmsTab()
            }
            override fun onTabUnselected(tab: TabLayout.Tab?) {}
            override fun onTabReselected(tab: TabLayout.Tab?) {}
        })
    }

    private fun showSmsTab() {
        binding.configPanel.visibility = View.VISIBLE
        binding.agentPanel.visibility = View.GONE
    }

    private fun showAgentTab() {
        binding.configPanel.visibility = View.GONE
        binding.agentPanel.visibility = View.VISIBLE
        updateAgentUi()
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

    private fun setupCodeEntry() {
        binding.buttonCodeAccess.setOnClickListener {
            val entered = binding.editCodeEntry.text?.toString()?.trim() ?: ""
            lifecycleScope.launch {
                val savedCode = prefs.configAccessCode.first()
                if (entered == savedCode) {
                    unlockedThisSession = true
                    binding.codeEntryPanel.visibility = View.GONE
                    binding.mainTabs.visibility = View.VISIBLE
                    showSmsTab()
                    loadSettingsSync()
                    setupListeners()
                    setupAgentListeners()
                } else {
                    Toast.makeText(this@MainActivity, "Code incorrect.", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private suspend fun loadSettingsSync() {
        binding.switchServiceEnabled.isChecked = prefs.serviceEnabled.first()
        binding.editApiUrl.setText(prefs.apiBaseUrl.first() ?: "")
        binding.editApiToken.setText(prefs.apiToken.first() ?: "")
        binding.editCodeConfig.setText(prefs.configAccessCode.first() ?: "")
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
        val codeConfig = binding.editCodeConfig.text?.toString()?.trim()
        val serviceEnabled = binding.switchServiceEnabled.isChecked

        if (serviceEnabled && (apiUrl.isBlank() || apiToken.isBlank())) {
            Toast.makeText(this, "URL de l'API et Token sont requis pour activer le service.", Toast.LENGTH_LONG).show()
            return
        }

        lifecycleScope.launch {
            prefs.saveAll(consent = true, serviceEnabled, apiUrl, apiToken, filterList = emptyList(), codeConfig)
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
                binding.mainTabs.getTabAt(0)?.select()
                return@launch
            }

            val identifiant = binding.editAgentIdentifiant.text?.toString()?.trim() ?: ""
            val password = binding.editAgentPassword.text?.toString() ?: ""

            if (identifiant.isBlank() || password.isBlank()) {
                Toast.makeText(this@MainActivity, "Identifiant et mot de passe requis.", Toast.LENGTH_SHORT).show()
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
        binding.textAgentStats.text = buildString {
            append("Aujourd'hui : ${fmt.format(stats?.todayTotal ?: 0.0)} F (${stats?.todayCount ?: 0} tx)\n")
            append("Ce mois : ${fmt.format(stats?.monthTotal ?: 0.0)} F (${stats?.monthCount ?: 0} tx)\n")
            append("Commission mois : ${fmt.format(stats?.monthCommission ?: 0.0)} F")
        }

        binding.agentTransactionsList.removeAllViews()
        val transactions = dashboard?.transactions.orEmpty()
        if (transactions.isEmpty()) {
            val empty = TextView(this).apply {
                text = "Aucune transaction."
                setPadding(0, 16, 0, 16)
            }
            binding.agentTransactionsList.addView(empty)
            return
        }

        transactions.forEach { tx ->
            binding.agentTransactionsList.addView(createTransactionRow(tx, offline))
        }
    }

    private fun createTransactionRow(tx: AgentTransaction, offline: Boolean): View {
        val fmt = NumberFormat.getNumberInstance(Locale.FRANCE)
        val container = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(0, 0, 0, 24)
        }

        val title = TextView(this).apply {
            text = "${tx.reference ?: "—"} · ${tx.type ?: ""} · ${fmt.format(tx.montant)} F"
            textSize = 15f
        }
        val subtitle = TextView(this).apply {
            text = "${tx.operateur ?: "N/A"} · ${tx.date ?: ""} · ${tx.statut ?: ""} · Commission ${fmt.format(tx.commission)} F"
            textSize = 13f
            alpha = 0.8f
        }
        container.addView(title)
        container.addView(subtitle)

        if (tx.canCancel && !offline) {
            val cancelBtn = Button(this).apply {
                text = getString(R.string.agent_cancel)
                setOnClickListener { confirmCancelTransaction(tx) }
            }
            container.addView(cancelBtn)
        }

        return container
    }

    private fun confirmCancelTransaction(tx: AgentTransaction) {
        val input = android.widget.EditText(this).apply {
            hint = "Raison de l'annulation"
        }
        AlertDialog.Builder(this)
            .setTitle("Annuler ${tx.reference}")
            .setMessage("Transaction de moins de 48 h. Confirmer l'annulation ?")
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
