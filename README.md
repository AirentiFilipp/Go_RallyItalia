# 🏎️ Rally Italia — Web App

Piattaforma collaborativa per visualizzare il calendario dei rally italiani, consultare classifiche e inserire i propri tempi per ogni Prova Speciale.

---

## 🗂️ Struttura del Progetto

```
rally-italia/
├── docker-compose.yml       # Orchestrazione container
├── Dockerfile               # Build immagine PHP+Apache
├── README.md
│
├── sql/
│   ├── schema.sql           # Struttura tabelle DB
│   └── seed.sql             # Dati di esempio
│
└── src/                     # Document root Apache
    ├── index.php            # Shell HTML principale (SPA)
    ├── api.php              # API REST PHP (routing)
    ├── config.php           # Config DB + helpers
    ├── style.css            # Tutti gli stili
    ├── script.js            # Frontend JS (fetch API)
    └── .htaccess            # Rewrite rules Apache
```

---

## 🚀 Avvio Rapido

### Prerequisiti
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installato e in esecuzione

### 1. Clona / scarica il progetto
```bash
git clone <repo-url> rally-italia
cd rally-italia
```

### 2. Avvia i container
```bash
docker-compose up -d
```
Al primo avvio Docker:
1. Scarica le immagini PHP+Apache e MySQL
2. Compila il container `app`
3. Crea il database `rally_italia` con schema e dati di esempio
4. Avvia tutto (~30 secondi la prima volta)

### 3. Apri l'app
| Servizio | URL |
|---|---|
| 🏎️ **Rally Italia** | http://localhost:8080 |
| 🗄️ phpMyAdmin | http://localhost:8081 |

---

## 🔐 Accesso Admin

Password demo: **`admin123`**

Clicca il badge **ADMIN OFF** in alto a destra per fare login.

Come admin puoi:
- ✅ Approvare / rifiutare i tempi in attesa
- ➕ Aggiungere nuove gare al calendario
- 🗑️ Eliminare gare e tempi
- ⚡ I tuoi tempi vengono approvati automaticamente

---

## 🛠️ API REST

Tutti gli endpoint accettano e restituiscono JSON.

### Autenticazione
| Metodo | Endpoint | Descrizione |
|---|---|---|
| POST | `/api/auth/login` | Login admin `{password}` |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/auth/status` | Stato sessione |

### Gare
| Metodo | Endpoint | Descrizione |
|---|---|---|
| GET | `/api/races` | Lista tutte le gare |
| POST | `/api/races` | Crea gara *(admin)* |
| GET | `/api/races/{id}` | Dettaglio gara + PS |
| PUT | `/api/races/{id}` | Modifica gara *(admin)* |
| DELETE | `/api/races/{id}` | Elimina gara *(admin)* |
| GET | `/api/races/{id}/stages` | Lista prove speciali |
| GET | `/api/races/{id}/times` | Tempi `?stage_id=X&category=Rally2` |

### Tempi
| Metodo | Endpoint | Descrizione |
|---|---|---|
| POST | `/api/times` | Inserisci tempo |
| PUT | `/api/times/{id}/approve` | Approva *(admin)* |
| DELETE | `/api/times/{id}` | Elimina *(admin)* |

---

## ⚙️ Configurazione

### Variabili d'ambiente (docker-compose.yml)
```yaml
DB_HOST: db
DB_NAME: rally_italia
DB_USER: rally_user
DB_PASS: rally_pass
ADMIN_PASSWORD: admin123   # ← cambia in produzione!
```

### Cambiare la password admin
Nel file `docker-compose.yml`, sotto `app > environment`:
```yaml
ADMIN_PASSWORD: la_tua_password_sicura
```
Poi: `docker-compose up -d --force-recreate app`

---

## 🔄 Comandi Utili

```bash
# Avvia
docker-compose up -d

# Ferma
docker-compose down

# Ferma e cancella tutti i dati DB
docker-compose down -v

# Vedi i log
docker-compose logs -f app

# Ricostruisci dopo modifiche al Dockerfile
docker-compose up -d --build

# Shell nel container PHP
docker exec -it rally_app bash

# Shell MySQL
docker exec -it rally_db mysql -u rally_user -prally_pass rally_italia
```

---

## 🗄️ Schema Database

```
races               → Gare del calendario
special_stages      → Prove Speciali (PS) di ogni gara
times               → Tempi inseriti dagli utenti
```

---

## 🚧 Roadmap / Idee Future

- [ ] Sistema di utenti con registrazione/login
- [ ] Upload foto dal percorso
- [ ] Grafici andamento tempi per pilota
- [ ] Notifiche push per nuovi tempi
- [ ] Export PDF classifica
- [ ] API pubblica per app mobile

---

## 📄 Licenza

Progetto demo / uso personale.
