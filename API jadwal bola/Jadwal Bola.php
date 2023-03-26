<?php
// API endpoint and your API key
$url = "https://api.football-data.org/v2/competitions/PL/matches";
$apiKey = "ab784a35cbb143c78bffee03a0c7e416";

// Set up HTTP headers
$headers = array(
    "X-Auth-Token: " . $apiKey,
    "Content-Type: application/json"
);

// Initialize cURL
$curl = curl_init();

// Set cURL options
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
));

// Execute the request
$response = curl_exec($curl);

// Check for errors
if(curl_error($curl)) {
    echo 'Error: ' . curl_error($curl);
    exit;
}

// Close the cURL resource
curl_close($curl);

// Decode the JSON response
$data = json_decode($response, true);

// Check if the response contains any errors
if(isset($data['errors'])) {
    echo 'Error: ' . $data['errors'][0]['detail'];
    exit;
}

// Set the default filter value
$filter = "all";

// Check if the filter has been submitted
if(isset($_GET['filter'])) {
    $filter = $_GET['filter'];
}

// Filter the matches by result
$matches = array_filter($data['matches'], function($match) use ($filter) {
    if($filter == "all") {
        return true;
    } else if($filter == "won") {
        return $match['score']['winner'] == "HOME_TEAM" || $match['score']['winner'] == "AWAY_TEAM";
    } else if($filter == "drawn") {
        return $match['score']['winner'] == null;
    } else if($filter == "lost") {
        return $match['score']['winner'] == "AWAY_TEAM" || $match['score']['winner'] == "HOME_TEAM";
    }
});

// Paginate the matches
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$perPage = 10;
$totalMatches = count($matches);
$totalPages = ceil($totalMatches / $perPage);
$offset = ($page - 1) * $perPage;
$matches = array_slice($matches, $offset, $perPage);

// Display the matches schedule using Bootstrap
?>
<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Pertandingan Bola</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Jadwal Pertandingan Bola</h1>
        <div class="mb-3">
            <form action="" method="get">
                <label for="filter">Filter by result:</label>
                <select name="filter" id="filter" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter == "all" ? "selected" : ""; ?>>Semua Pertandingan</option>
                    <option value="won" <?php echo $filter == "won" ? "selected" : ""; ?>>Menang</option>
                    <option value="drawn" <?php echo $filter == "drawn" ? "selected" : ""; ?>>Seri</option>
                    <option value="lost" <?php echo $filter == "lost" ? "selected" : ""; ?>>Kalah</option>
                    </select>
        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal (hari/bulan/tahun)</th>
                <th>Tim Tuan Rumah</th>
                <th>Tim Tandang</th>
                <th>Skor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($matches as $match): ?>
            <tr>
                <td><?php echo date('d-m-Y', strtotime($match['utcDate'])); ?></td>
                <td><?php echo $match['homeTeam']['name']; ?></td>
                <td><?php echo $match['awayTeam']['name']; ?></td>
                <td>
                    <?php if($match['status'] == 'FINISHED'): ?>
                        <?php echo $match['score']['fullTime']['homeTeam']; ?> - <?php echo $match['score']['fullTime']['awayTeam']; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if($totalPages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
<!-- Bootstrap JavaScript -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>