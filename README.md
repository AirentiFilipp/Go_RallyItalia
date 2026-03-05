# 🏎️ Rally Italia - Web App

## 📌 Descrizione del Progetto
**Rally Italia** è una piattaforma collaborativa dove gli appassionati possono visualizzare il calendario dei rally italiani, consultare i risultati e inserire i propri tempi o quelli dei loro piloti preferiti per ogni Prova Speciale (PS).

---

## ✨ Funzionalità Aggiuntive Integrate
Per rendere l'app veramente utile per i fan del rally, ho previsto queste funzioni:
1. **Divisione per Categoria/Classe:** I tempi nel rally dipendono dall'auto (es. WRC, Rally2, Rally4). Una classifica unificata non avrebbe senso.
2. **Condizioni Meteo e Fondo:** Possibilità di specificare le condizioni (es. Asfalto Asciutto, Terra Bagnata, Neve).
3. **Calcolo del Distacco:** Calcolo automatico del ritardo rispetto al primo in classifica (+X.X secondi).
4. **Sistema di Ruoli:** Gli utenti normali inseriscono i tempi, ma tu (Admin) puoi moderarli, modificarli o approvarli per evitare inserimenti falsi o spam.

---

## 🖥️ Struttura dell'Interfaccia (Mockup)

### 🏠 Homepage: Calendario Gare Attive
Qui gli utenti vedono le gare in corso o in programma.

| Data | Gara | Località | Fondo | Stato | Azioni |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **15-16 Apr** | *Rally di Sanremo* | Liguria | Asfalto | 🟢 In Corso | [Vedi Classifica] [Inserisci Tempo] |
| **02-04 Mag** | *Rally Costa Smeralda* | Sardegna | Terra | 🟡 Prossima | [Dettagli Gara] |
| **20-21 Mag** | *Rally del Salento* | Puglia | Asfalto | 🟡 Prossima | [Dettagli Gara] |

---

### ⏱️ Pagina Gara: Rally di Sanremo
**Dettagli:** ☀️ Soleggiato | 🛣️ Asfalto Asciutto

#### Seleziona Prova Speciale:
> **[ PS1: "Langan" (14.5 km) 🔽 ]** *(Menu a tendina per cambiare prova)*

#### 🏆 Classifica Live - Categoria: Rally2
| Pos | Utente / Pilota | Auto | Tempo | Distacco |
| :---: | :--- | :--- | :--- | :--- |
| 🥇 | @Marco_99 | Skoda Fabia RS | **10:45.2** | - |
| 🥈 | @TuoNome (Admin)| Toyota GR Yaris | **10:48.5** | +3.3s |
| 🥉 | @RallyFanITA | Citroën C3 | **10:51.0** | +5.8s |
| 4 | @Luca_Drift | Hyundai i20 N | **11:02.1** | +16.9s |

---

### ➕ Modulo: Inserisci un Tempo
Questa è la schermata che userai tu e gli utenti per aggiungere i dati.

**Gara:** Rally di Sanremo
**Prova Speciale:** [ PS1: Langan 🔽 ]

* **Nome Pilota/Utente:** `[ Inserisci nome ]`
* **Vettura:** `[ Es. Skoda Fabia ]`
* **Categoria:** `[ Rally2 🔽 ]`
* **Tempo Effettuato:**
  * Minuti: `[ 10 ]`
  * Secondi: `[ 45 ]`
  * Decimi: `[ 2 ]`

**[ 💾 SALVA TEMPO ]** *(Pulsante)*

---

## 🛠️ Tecnologie Consigliate per lo Sviluppo
* **Frontend:** HTML, CSS, JavaScript (magari con un framework leggero come Vue.js o React per l'aggiornamento in tempo reale delle classifiche).
* **Backend & Database:** Firebase o Supabase. Sono perfetti per questo progetto perché permettono di aggiornare i tempi in *tempo reale* su tutti i dispositivi senza dover ricaricare la pagina.
