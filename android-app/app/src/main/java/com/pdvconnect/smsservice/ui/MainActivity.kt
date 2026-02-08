package com.pdvconnect.smsservice.ui

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import com.pdvconnect.smsservice.data.AppPreferences
import com.pdvconnect.smsservice.databinding.ActivityMainBinding
import com.pdvconnect.smsservice.sms.SmsForwarderService
import androidx.lifecycle.lifecycleScope
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var prefs: AppPreferences
    private var unlockedThisSession = false

    private val requestPermissions = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { result ->
        val allGranted = result.values.all { it }
        if (!allGranted) {
            Toast.makeText(this, "Les permissions SMS sont nécessaires pour transférer les transactions.", Toast.LENGTH_LONG).show()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        prefs = AppPreferences(this)

        requestPermissionsIfNeeded()
        lifecycleScope.launch {
            val code = prefs.configAccessCode.first()
            if (!code.isNullOrBlank() && !unlockedThisSession) {
                binding.codeEntryPanel.visibility = View.VISIBLE
                binding.configPanel.visibility = View.GONE
                setupCodeEntry()
            } else {
                binding.codeEntryPanel.visibility = View.GONE
                binding.configPanel.visibility = View.VISIBLE
                loadSettingsSync()
                setupListeners()
            }
        }
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
                    binding.configPanel.visibility = View.VISIBLE
                    loadSettingsSync()
                    setupListeners()
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
        }
    }

    private fun startForegroundServiceIfNeeded() {
        val intent = Intent(this, SmsForwarderService::class.java)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            startForegroundService(intent)
        } else {
            startService(intent)
        }
    }

}
