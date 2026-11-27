<?php
require "connect_db.php";

$karnety = $connection->query("SELECT * FROM typykarnetow");
$zajecia = $connection->query("SELECT z.id_zajec, z.nazwa_zajec, t.imie, t.nazwisko 
                         FROM zajeciagrupowe z
                         JOIN trenerzy t ON z.id_trenera = t.id_trenera");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikacja siłowni</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form method="post">

    <h2>Dane klienta</h2>

    <input type="text" name="imie" placeholder="Imię" required><br>
    <input type="text" name="nazwisko" placeholder="Nazwisko" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="date" name="data_urodzenia" required><br>

    <h3>Wybierz karnet</h3>

    <select name="id_typu" required>
        <?php while($k = $karnety->fetch_assoc()): ?>
            <option value="<?= $k['id_typu'] ?>">
                <?= $k['nazwa_karnetu'] ?> (<?= $k['cena'] ?> zł)
            </option>
        <?php endwhile; ?>
    </select>

    <h3>Zajęcia (opcjonalnie)</h3>

    <select name="id_zajec">
        <option value="">Brak</option>
        <?php while($z = $zajecia->fetch_assoc()): ?>
            <option value="<?= $z['id_zajec'] ?>">
                <?= $z['nazwa_zajec'] ?> - <?= $z['imie'] ?> <?= $z['nazwisko'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>
    <input type="submit" name="dodaj" value="Dodaj klienta">

</form>

<?php
if (isset($_POST['dodaj'])) {

    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $email = trim($_POST['email']);
    $data = $_POST['data_urodzenia'];
    $id_typu = (int)$_POST['id_typu'];
    $id_zajec = !empty($_POST['id_zajec']) ? (int)$_POST['id_zajec'] : null;

    // 1. dodanie klienta
    $stmt = $connection->prepare("
        INSERT INTO klienci (imie, nazwisko, email, data_urodzenia)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $imie, $nazwisko, $email, $data);
    $stmt->execute();

    $id_klienta = $connection->insert_id;

    // 2. pobranie dni ważności
    $stmt = $connection->prepare("
        SELECT dni_waznosci FROM typykarnetow WHERE id_typu = ?
    ");
    $stmt->bind_param("i", $id_typu);
    $stmt->execute();

    $result = $stmt->get_result();
    $dni = $result->fetch_assoc()['dni_waznosci'];

    // 3. dodanie karnetu
    $data_start = date("Y-m-d");
    $data_koniec = date("Y-m-d", strtotime("+$dni days"));

    $stmt = $connection->prepare("
        INSERT INTO zakupionekarnety 
        (id_klienta, id_typu, data_aktywacji, data_waznosci, status)
        VALUES (?, ?, ?, ?, 'aktywny')
    ");
    $stmt->bind_param("iiss", $id_klienta, $id_typu, $data_start, $data_koniec);
    $stmt->execute();

    // 4. zapis na zajęcia
    if ($id_zajec !== null) {
        $stmt = $connection->prepare("
            INSERT INTO klient_zajecia (id_klienta, id_zajec)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $id_klienta, $id_zajec);
        $stmt->execute();
    }

    echo "Dodano klienta + karnet + zajęcia";
}
?>

<details>
<summary>Lista klientów</summary>

<ul>
<?php
$result = $connection->query("
    SELECT CONCAT(imie, ' ', nazwisko) AS klient
    FROM klienci
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['klient']}</li>";
}
?>
</ul>
</details>

<details>
<summary>Karnety klientów</summary>
<ul>
<?php
$result = $connection->query("
SELECT k.imie, k.nazwisko, t.nazwa_karnetu, z.data_waznosci
FROM klienci k
JOIN zakupionekarnety z ON k.id_klienta = z.id_klienta
JOIN typykarnetow t ON z.id_typu = t.id_typu
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['imie']} {$row['nazwisko']} - {$row['nazwa_karnetu']}</li>";
}
?>
</ul>
</details>

<details>
<summary>Aktywne karnety</summary>
<ul>
<?php
$result = $connection->query("
SELECT k.imie, k.nazwisko
FROM klienci k
JOIN zakupionekarnety z ON k.id_klienta = z.id_klienta
WHERE z.status = 'aktywny'
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['imie']}  {$row['nazwisko']}</li>";
}
?>
</ul>
</details>

<details>
<summary>Ilość wejść</summary>
<ul>
<?php
$result = $connection->query("
SELECT k.imie, k.nazwisko, COUNT(w.id_wejscia) AS wejscia
FROM klienci k
JOIN wejscia w ON k.id_klienta = w.id_klienta
GROUP BY k.id_klienta
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['imie']} {$row['nazwisko']} - {$row['wejscia']} wejść</li>";
}
?>
</ul>
</details>

<details>
<summary>Najaktywniejszy klient</summary>
<ul>
<?php
$result = $connection->query("
SELECT k.imie, k.nazwisko, COUNT(w.id_wejscia) AS wejscia
FROM klienci k
JOIN wejscia w ON k.id_klienta = w.id_klienta
GROUP BY k.id_klienta
ORDER BY wejscia DESC
LIMIT 1
");

$row = $result->fetch_assoc();
echo "<li>{$row['imie']} {$row['nazwisko']} - {$row['wejscia']} wejść </li>";
?>
</ul>
</details>

<details>
<summary>Zajęcia</summary>
<ul>
<?php
$result = $connection->query("
SELECT z.nazwa_zajec, t.imie, t.nazwisko
FROM zajeciagrupowe z
JOIN trenerzy t ON z.id_trenera = t.id_trenera
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['nazwa_zajec']} - {$row['imie']} {$row['nazwisko']}</li>";
}
?>
</ul>
</details>

<details>
<summary>Uczestnicy zajęć</summary>
<ul>
<?php
$result = $connection->query("
SELECT z.nazwa_zajec, COUNT(kz.id_klienta) AS liczba
FROM zajeciagrupowe z
LEFT JOIN klient_zajecia kz ON z.id_zajec = kz.id_zajec
GROUP BY z.id_zajec
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['nazwa_zajec']} - {$row['liczba']} osób</li>";
}
?>
</ul>
</details>

<details>
<summary>Wolne miejsca</summary>
<ul>
<?php
$result = $connection->query("
SELECT z.nazwa_zajec, z.max_osob, COUNT(kz.id_klienta) AS zapisani
FROM zajeciagrupowe z
LEFT JOIN klient_zajecia kz ON z.id_zajec = kz.id_zajec
GROUP BY z.id_zajec
HAVING zapisani < z.max_osob
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['nazwa_zajec']} - wolne miejsca</li>";
}
?>
</ul>
</details>

<details>
<summary>Sprzęt</summary>
<ul>
<?php
$result = $connection->query("
    SELECT 
        s.nazwa_sali, 
        sp.nazwa_maszyny, 
        COUNT(*) AS ilosc
    FROM sprzet sp
    JOIN sale s ON sp.id_sali = s.id_sali
    GROUP BY s.nazwa_sali, sp.nazwa_maszyny
    ORDER BY s.nazwa_sali, ilosc DESC
");

while ($row = $result->fetch_assoc()) {
    echo "<li>{$row['nazwa_sali']} - {$row['nazwa_maszyny']} ({$row['ilosc']})</li>";
}
?>
</ul>
</details>

<details>
<summary>Najdroższy karnet</summary>
<ul>
<?php
$result = $connection->query("
SELECT nazwa_karnetu, cena
FROM typykarnetow
ORDER BY cena DESC
LIMIT 1
");

$row = $result->fetch_assoc();
echo "<li>{$row['nazwa_karnetu']} - {$row['cena']} zł</li>";
?>
</ul>
</details>

<details>
<summary>Ranking klientów</summary>
<ul>
<?php
$result = $connection->query("
SELECT k.imie, k.nazwisko,
COUNT(w.id_wejscia) AS wejscia
FROM klienci k
JOIN wejscia w ON k.id_klienta = w.id_klienta
GROUP BY k.id_klienta
ORDER BY wejscia DESC
LIMIT 10
");

$rank = 1;

while ($row = $result->fetch_assoc()) {
    echo "<li>{$rank} {$row['imie']} {$row['nazwisko']} - {$row['wejscia']}</li>";
    $rank++;
}
?>
</ul>
</details>
<a href="schemat.html" target="_blank">strona ze schematem</a>
</body>
</html>