# AI Conversation Agent (Chatbot)

>  Work in progress

Stateful və genişlənə bilən **AI conversation agent**. Məqsəd sadə chatbot yox, agent-based conversation flow qurmaqdır.

---

##  Nədir?

* Session-aware AI chat
* Short-term (history) + long-term (summary) memory
* HTTP / WebSocket üçün hazır agent layer

---

##  Arxitektura

```text
Controller (HTTP adapter)
        ↓
ConversationAgent (orchestrator)
        ↓
AIService      ConversationMemoryService
```

---

##  Hazırda var

* Agent-based conversation flow
* Clean controller (business logic yoxdur)
* Session-based memory
* Stabil response contract (`AgentResponse`)

---

##  Roadmap

* WebSocket real-time chat
* Vue.js minimal UI
* Agent tool / action calling

---

##  Tech

Laravel · PHP 8+ · OpenAI API

---

Feedback və töhfələr açıqdır.
