package com.pdvconnect.smsservice.ui

import android.content.Context
import android.graphics.BitmapFactory
import android.widget.ImageView
import androidx.annotation.ColorRes
import androidx.annotation.DrawableRes
import androidx.core.content.ContextCompat
import com.pdvconnect.smsservice.R
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.net.URL

object OperateurBranding {

  private fun blob(code: String?, libelle: String?): String {
    return "${code.orEmpty()} ${libelle.orEmpty()}".trim().uppercase()
  }

  @DrawableRes
  fun logoDrawable(code: String?, libelle: String? = null): Int? {
    val key = blob(code, libelle)
    return when {
      key.contains("FLOOZ") || key.contains("MOOV") -> R.drawable.logo_operateur_flooz
      key.contains("YAS") || key.contains("MIX") -> R.drawable.logo_operateur_yas
      else -> null
    }
  }

  @ColorRes
  fun accentColorRes(code: String?, libelle: String? = null): Int {
    return when (logoDrawable(code, libelle)) {
      R.drawable.logo_operateur_flooz -> R.color.flooz_blue
      R.drawable.logo_operateur_yas -> R.color.yas_brown
      else -> R.color.primary
    }
  }

  fun accentColor(context: Context, code: String?, libelle: String? = null): Int {
    return ContextCompat.getColor(context, accentColorRes(code, libelle))
  }

  fun bindLogo(
    imageView: ImageView,
    scope: CoroutineScope,
    logoUrl: String?,
    operatorCode: String?,
    operatorLibelle: String?,
  ) {
    logoDrawable(operatorCode, operatorLibelle)?.let(imageView::setImageResource)
    val url = logoUrl?.trim().orEmpty()
    if (url.isEmpty()) return
    scope.launch {
      val bitmap = withContext(Dispatchers.IO) {
        runCatching {
          URL(url).openStream().use { stream ->
            BitmapFactory.decodeStream(stream)
          }
        }.getOrNull()
      } ?: return@launch
      imageView.setImageBitmap(bitmap)
    }
  }
}
