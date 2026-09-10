package com.pdvconnect.smsservice.util

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNull
import org.junit.Test

class AgentContextProviderTest {

    @Test
    fun smsAgentContext_usesSimPhone_notSessionAgent() {
        val ctx = AgentContextProvider.smsAgentContext(
            simPhone = "+22890123456",
            smsAgentCode = "5150328",
        )

        assertNull(ctx.agentId)
        assertEquals("5150328", ctx.agentCode)
        assertEquals("+22890123456", ctx.agentTelephone)
    }

    @Test
    fun smsAgentContext_blankSim_sendsNullTelephone() {
        val ctx = AgentContextProvider.smsAgentContext(
            simPhone = "   ",
            smsAgentCode = null,
        )

        assertNull(ctx.agentId)
        assertNull(ctx.agentCode)
        assertNull(ctx.agentTelephone)
    }
}
