console.log("Script caricato correttamente!");

document.getElementById('rallyForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const pilot = document.getElementById('pilot').value;
    const car = document.getElementById('car').value;
    const time = document.getElementById('time').value;

    const tbody = document.getElementById('leaderboard');
    const row = document.createElement('tr');

    row.innerHTML = `<td>${pilot}</td><td>${car}</td><td>${time}</td>`;
    tbody.prepend(row);

    this.reset();
    alert("Tempo inserito!");
});
