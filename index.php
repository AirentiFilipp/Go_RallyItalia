<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏁 Rally della Liguria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-container">
        <header>
            <h1># 🏁 Rally della Liguria</h1>
            <p>Se vedi questa scritta, l'HTML sta caricando correttamente.</p>
        </header>

        <section class="info">
            <h2>🌍 La Location</h2>
            <ul>
                <li>🌊 Strade panoramiche vista mare</li>
                <li>⛰️ Percorsi tecnici tra le montagne</li>
            </ul>
        </section>

        <section class="results">
            <h2>🏎️ Classifica Live</h2>
            <table>
                <thead>
                    <tr>
                        <th>Pilota</th>
                        <th>Vettura</th>
                        <th>Tempo</th>
                    </tr>
                </thead>
                <tbody id="leaderboard">
                    <tr>
                        <td>Esempio Pilota</td>
                        <td>Skoda Fabia</td>
                        <td>08:45.00</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="form-box">
            <h3>⏱️ Inserisci Tempo</h3>
            <form id="rallyForm">
                <input type="text" id="pilot" placeholder="Nome" required>
                <input type="text" id="car" placeholder="Auto" required>
                <input type="text" id="time" placeholder="MM:SS.ms" required>
                <button type="submit">Invia</button>
            </form>
        </section>
    </div>
    <script src="script.js"></script>
</body>
</html>