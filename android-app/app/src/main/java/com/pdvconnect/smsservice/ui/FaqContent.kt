package com.pdvconnect.smsservice.ui

import androidx.annotation.StringRes
import com.pdvconnect.smsservice.R

object FaqContent {

    data class Item(
        @StringRes val questionRes: Int,
        @StringRes val answerRes: Int,
    )

    val items: List<Item> = listOf(
        Item(R.string.faq_q_login, R.string.faq_a_login),
        Item(R.string.faq_q_sms_service, R.string.faq_a_sms_service),
        Item(R.string.faq_q_agent_id, R.string.faq_a_agent_id),
        Item(R.string.faq_q_format, R.string.faq_a_format),
        Item(R.string.faq_q_offline, R.string.faq_a_offline),
        Item(R.string.faq_q_pending, R.string.faq_a_pending),
        Item(R.string.faq_q_notifications, R.string.faq_a_notifications),
        Item(R.string.faq_q_password, R.string.faq_a_password),
        Item(R.string.faq_q_update, R.string.faq_a_update),
        Item(R.string.faq_q_reboot, R.string.faq_a_reboot),
    )
}
