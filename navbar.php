<div href="#" class="brand" style="text-align:center; font-size:38px; margin-top: 15px;"><i class='bx bxs-file-doc icon'></i> ProfTal</div>
<ul class="side-menu">
    <li><a href="dash.php" class="active"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
    <li class="divider" data-text="main">Main</li>
    <li><a href="subjects.php"><i class='bx bxs-bookmarks icon'></i>All Subjects</a></li>
    <li >
        <a href="#"style="background-color:#9B2035;color:#ffff;"><i class='bx bxs-bookmark icon' ></i> Subjects </a>
        <ul class="side-dropdown1">
            <?php
            // Include your database connection file here
            include 'connection.php';
            $user_id = $_SESSION['user_id']; // Initialize $user_id from session

            // Query to fetch subjects from feedcontent table
            $sql_subjects = "SELECT fs.ContentID, fs.Title, fs.Captions
                            FROM feedcontent fs
                            INNER JOIN usercontent uc ON fs.ContentID = uc.ContentID
                            WHERE uc.UserID = $user_id AND uc.Status=1";
            $result_subjects = mysqli_query($conn, $sql_subjects);

            // Check if subjects are found
            if (mysqli_num_rows($result_subjects) > 0) {
                while ($row_subject = mysqli_fetch_assoc($result_subjects)) {
                    $subject_id = $row_subject['ContentID'];
                    $subject_name = $row_subject['Title'];
                    // Split the subject name into words
                    $words = explode(' ', $subject_name);
                    // Get the first letter of the first word and the first letter of the second word
                    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

                    echo "<li>";
                    echo "<div class='subject-container'>";
                    echo "<div class='initial-circle'>$initials</div>"; // Circle with initials
                    echo "<a href='tasks.php?content_id=$subject_id'>$subject_name</a>";
                    echo "</div>";
                    echo "</li>";
                }
            } else {
                echo "<li><a href='#'>No subjects found</a></li>";
            }

            // Close database connection
            mysqli_close($conn);
            ?>
        </ul>
    </li>
    
    <li class="divider" data-text="others">Others   </li>
    <li><a href="#"><i class='bx bx-table icon'></i> Archived Subjects</a></li>
    <li>
        <a href="#"><i class='bx bxs-notepad icon'></i> Forms <i class='bx bx-chevron-right icon-right'></i></a>
        <ul class="side-dropdown">
            <li><a href="#">Basic</a></li>
            <li><a href="#">Select</a></li>
            <li><a href="#">Checkbox</a></li>
            <li><a href="#">Radio</a></li>
        </ul>
    </li>
</ul>

<style>
.side-dropdown1 {
    padding-left: 10px; /* Adjust the padding to move subjects to the left */
}

.subject-container {
    display: flex;
    align-items: center;
}

.subject-container a {
    margin-left: 10px;
    text-decoration: none;
    color: #555;
    font-weight:bold;
}

.initial-circle {
    width: 36px;
    height: 36px;
    background-color: #9B2035;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    font-weight: bold;
    font-size: 14px;
    margin-right:10px;
}

/* Adjust margins, paddings, and colors as per your design */
</style>
