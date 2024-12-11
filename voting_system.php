<?php
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "voting_system"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle candidate addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['candidate_name'])) {
    $candidate_name = $conn->real_escape_string($_POST['candidate_name']);

    // Insert candidate into the database
    $stmt = $conn->prepare("INSERT INTO candidates (name) VALUES (?)");
    $stmt->bind_param("s", $candidate_name);

    if ($stmt->execute()) {
        $message = "Candidate added successfully.";
    } else {
        $message = "Error adding candidate: " . $conn->error;
    }

    $stmt->close();
}

// Handle vote submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vote_candidate_id'])) {
    $candidate_id = intval($_POST['vote_candidate_id']);

    // Insert vote into the database
    $stmt = $conn->prepare("INSERT INTO votes (candidate_id) VALUES (?)");
    $stmt->bind_param("i", $candidate_id);

    if ($stmt->execute()) {
        $vote_message = "Vote recorded successfully.";
    } else {
        $vote_message = "Error recording vote: " . $conn->error;
    }

    $stmt->close();
}

// Fetch candidates
$sql_candidates = "SELECT * FROM candidates";
$result_candidates = $conn->query($sql_candidates);

if ($result_candidates === FALSE) {
    die("Error fetching candidates: " . $conn->error);
}

// Fetch votes and count
$sql_results = "SELECT candidates.name, COUNT(votes.id) AS vote_count
                FROM candidates
                LEFT JOIN votes ON candidates.id = votes.candidate_id
                GROUP BY candidates.id";
$result_results = $conn->query($sql_results);

if ($result_results === FALSE) {
    die("Error fetching results: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Vote for Your Favorite Candidate</h1>
        <?php if (isset($vote_message)) { echo "<p class='message'>$vote_message</p>"; } ?>
        
        <form action="" method="post" class="form-section">
            <h2>Add a New Candidate</h2>
            <input type="text" name="candidate_name" placeholder="Candidate Name" required>
            <input type="submit" value="Add Candidate">
            <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
        </form>
        
        <form action="" method="post" class="form-section">
            <h2>Cast Your Vote</h2>
            <?php
            if ($result_candidates->num_rows > 0) {
                while ($row = $result_candidates->fetch_assoc()) {
                    echo '<label><input type="radio" name="vote_candidate_id" value="' . $row['id'] . '"> ' . $row['name'] . '</label><br>';
                }
            } else {
                echo "<p>No candidates found.</p>";
            }
            ?>
            <input type="submit" value="Vote">
        </form>

        <h2>Voting Results</h2>
        <div class="results-section">
            <?php
            if ($result_results->num_rows > 0) {
                while ($row = $result_results->fetch_assoc()) {
                    echo '<p>' . $row['name'] . ': <span class="vote-count">' . $row['vote_count'] . '</span> votes</p>';
                }
            } else {
                echo "<p>No votes yet.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
