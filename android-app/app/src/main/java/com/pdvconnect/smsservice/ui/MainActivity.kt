package com.pdvconnect.smsservice.ui

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.view.View
import android.view.ViewGroup
import android.widget.EditText
import android.widget.ImageButton
import android.widget.LinearLayout
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.setPadding
import com.google.android.material.textfield.TextInputLayout
import com.pdvconnect.smsservice.R
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

        askPermissionsIfNeeded()
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
        binding.switchConsent.isChecked = prefs.consentAccepted.first()
        binding.switchServiceEnabled.isChecked = prefs.serviceEnabled.first()
        binding.editApiUrl.setText(prefs.apiBaseUrl.first() ?: "")
        binding.editApiToken.setText(prefs.apiToken.first() ?: "")
        binding.editCodeConfig.setText(prefs.configAccessCode.first() ?: "")

        val filters = prefs.filterList.first()
        binding.filtersContainer.removeAllViews()
        if (filters.isEmpty()) {
            addFilterRow("")
        } else {
            filters.forEach { addFilterRow(it) }
        }
    }

    private fun addFilterRow(value: String = "") {
        val dp8 = (8 * resources.displayMetrics.density).toInt()
        val row = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            ).apply { bottomMargin = dp8 }
        }

        val inputLayout = TextInputLayout(this, null, com.google.android.material.R.attr.materialOutlinedTextInputStyle).apply {
            layoutParams = LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f)
            hint = getString(R.string.filter_number_hint)
            setPadding(dp8)
        }
        val editText = EditText(this).apply {
            setText(value)
            setPadding(dp8)
            hint = "Ex: FLOOZ, +22507123456"
        }
        inputLayout.addView(editText)

        val removeBtn = ImageButton(this).apply {
            setImageResource(android.R.drawable.ic_menu_delete)
            setOnClickListener {
                if (binding.filtersContainer.childCount > 1) {
                    binding.filtersContainer.removeView(row)
                }
            }
            setPadding(dp8)
        }

        row.addView(inputLayout)
        row.addView(removeBtn)
        binding.filtersContainer.addView(row)
    }

    private fun setupListeners() {
        binding.buttonAddFilter.setOnClickListener { addFilterRow("") }
        binding.buttonSave.setOnClickListener { saveAndMaybeStartService() }
    }

    private fun collectFilterList(): List<String> {
        val list = mutableListOf<String>()
        for (i in 0 until binding.filtersContainer.childCount) {
            val row = binding.filtersContainer.getChildAt(i) as? LinearLayout ?: continue
            val inputLayout = row.getChildAt(0) as? TextInputLayout ?: continue
            val edit = inputLayout.editText ?: continue
            val text = edit.text?.toString()?.trim() ?: ""
            if (text.isNotBlank()) list.add(text)
        }
        return list
    }

    private fun saveAndMaybeStartService() {
        val apiUrl = binding.editApiUrl.text?.toString()?.trim() ?: ""
        val apiToken = binding.editApiToken.text?.toString()?.trim() ?: ""
        val filterList = collectFilterList()
        val codeConfig = binding.editCodeConfig.text?.toString()?.trim()
        val consent = binding.switchConsent.isChecked
        val serviceEnabled = binding.switchServiceEnabled.isChecked

        if (serviceEnabled && (apiUrl.isBlank() || apiToken.isBlank())) {
            Toast.makeText(this, "URL de l'API et Token sont requis pour activer le service.", Toast.LENGTH_LONG).show()
            return
        }

        if (serviceEnabled && !consent) {
            Toast.makeText(this, "Veuillez accepter les conditions de confidentialité.", Toast.LENGTH_LONG).show()
            return
        }

        lifecycleScope.launch {
            prefs.saveAll(consent, serviceEnabled, apiUrl, apiToken, filterList, codeConfig)
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
