<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

$student_id = $_SESSION['student_id'];

$student_sql = "SELECT * FROM student WHERE id='$student_id'";
$student_result = mysqli_query($conn, $student_sql);
$student = mysqli_fetch_assoc($student_result);

$student_cgpa = $student['cgpa'];

$company_sql = "
SELECT
    company_name,
    MAX(logo) AS logo,
    location,
    COUNT(*) AS total_roles
FROM company
GROUP BY company_name, location
ORDER BY company_name
";

$company_result = mysqli_query($conn, $company_sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Companies</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="navbar">

        <div class="nav-left">

            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="companies.php" class="active">Companies</a>
            <a href="prediction.php">AI Prediction</a>
            <a href="placement_status.php">Status</a>
            <a href="placement_results.php">Results</a>
            <a href="interview_schedule.php">
                📅 Interviews
            </a>
            <a href="upload_resume.php">Upload Resume</a>
            <a href="job_recommendations.php">Job Recommendations</a>

        </div>

        <div class="nav-right">

            <a href="logout.php"
                onclick="return confirm('Are you sure you want to logout?');">
                Logout
            </a>

        </div>

    </div>
    <div class="card">

        <h2>Available Companies</h2>
        <div class="search-area">

            <input
                type="text"
                id="companySearch"
                placeholder="Search Company..."
                onkeyup="filterCompanies()">

            <select id="locationFilter" onchange="filterCompanies()">

                <option value="">All Locations</option>

                <option value="Bangalore">Bangalore</option>

                <option value="Hyderabad">Hyderabad</option>

                <option value="Mangalore">Mangalore</option>

            </select>

        </div>

        <div class="company-container">

            <?php
            while ($company = mysqli_fetch_assoc($company_result)) {

            ?>
                <div class="company-card"
                    data-company="<?php echo strtolower($company['company_name']); ?>"
                    data-location="<?php echo strtolower($company['location']); ?>">

                    <div class="company-header">

                        <?php

                        $logo = !empty($company['logo'])
                            ? "../uploads/company_logo/" . $company['logo']
                            : "../uploads/company_logo/default.jpg";

                        ?>

                       <img
    src="<?php echo $logo; ?>"
    class="company-logo"
    alt="Company Logo">

                        <div>

                            <h2 class="company-name">
                                <?php echo htmlspecialchars($company['company_name']); ?>
                            </h2>

                            <p class="company-role">

                                📍
                                <?php
                                echo !empty($company['location'])
                                    ? htmlspecialchars($company['location'])
                                    : "Location Not Available";
                                ?>

                            </p>

                        </div>

                    </div>

                    <div class="company-info">

                        <div class="info-box">

                            <span class="label">Open Positions</span>

                            <strong>

                                <?php echo $company['total_roles']; ?>

                            </strong>

                        </div>

                    </div>

                    <div class="company-buttons">

                        <button

                            class="btn details-btn"

                            onclick="loadCompanyRoles('<?php echo htmlspecialchars($company['company_name']); ?>')">

                            View Openings

                        </button>

                    </div>

                </div>
            <?php
            }
            ?>

        </div>

        <br>

        <a class="btn" href="dashboard.php">⬅ Back to Dashboard</a>

    </div>

    <!-- Company Roles Modal -->

    <div id="companyModal" class="modal">

        <div class="modal-content">

            <span class="close" onclick="closeCompanyModal()">&times;</span>

            <h2 id="modalTitle">Company Openings</h2>

            <hr>

            <div id="rolesContainer">

                Loading...

            </div>

            <div id="roleDetailsContainer" style="display:none;">

            </div>

        </div>

    </div>
    <script>
        function loadCompanyRoles(companyName) {

            let modal = document.getElementById("companyModal");

            modal.classList.add("show");

            document.getElementById("modalTitle").innerText =
                companyName + " - Open Positions";

            document.getElementById("rolesContainer").innerHTML =
                "Loading...";

            document.getElementById("rolesContainer").style.display = "block";

            document.getElementById("roleDetailsContainer").style.display = "none";


            fetch("get_company_roles.php?company=" +
                    encodeURIComponent(companyName))

                .then(response => response.text())

                .then(data => {

                    document.getElementById("rolesContainer").innerHTML =
                        data;

                });

        }

        function showRoleDetails(button) {

            document.getElementById("rolesContainer").style.display = "none";

            let html = "";

            html += "<button class='btn' onclick='backToRoles()'>⬅ Back to Openings</button>";

            html += "<h2>" + button.dataset.company + "</h2>";
            html += "<h3>" + button.dataset.role + "</h3>";

            html += "<hr>";

            html += "<div class='details-grid'>";

            html += "<div class='detail-box'>";
            html += "<span>💰 Package</span>";
            html += "<strong>: " + button.dataset.package + " LPA</strong>";
            html += "<strong>" + button.dataset.package + " LPA</strong>";
            html += "</div>";

            html += "<div class='detail-box'>";
            html += "<span>🎓 Minimum CGPA</span>";
            html += "<strong>" + button.dataset.cgpa + "</strong>";
            html += "</div>";

            html += "<div class='detail-box'>";
            html += "<span>📍 Location</span>";
            html += "<strong>" + (button.dataset.location || "Not Available") + "</strong>";
            html += "</div>";

            html += "<div class='detail-box'>";
            html += "<span>💼 Experience</span>";
            html += "<strong>" + (button.dataset.experience || "Not Available") + "</strong>";
            html += "</div>";

            html += "</div>";

            html += "<h3>Required Skills</h3>";

            if (button.dataset.skills && button.dataset.skills.trim() !== "") {

                let skills = button.dataset.skills.split(",");

                skills.forEach(function(skill) {

                    html += "<span class='skill-badge'>" +
                        skill.trim() +
                        "</span> ";

                });

            } else {

                html += "<p>Not Available</p>";

            }

            html += "<h3 style='margin-top:20px;'>Job Description</h3>";

            html += "<div class='job-description'>" +
                (button.dataset.description || "Not Available") +
                "</div>";

            html += "<br><br>";

            if (button.dataset.applied === "yes") {

                html += "<button class='btn' disabled>Already Applied</button>";

            } else {

                html += "<a class='btn' href='apply.php?company_id=" +
                    button.dataset.companyid +
                    "'>Apply</a>";

            }

            document.getElementById("roleDetailsContainer").innerHTML = html;

            document.getElementById("roleDetailsContainer").style.display = "block";

        }

        function backToRoles() {

            document.getElementById("roleDetailsContainer").style.display = "none";

            document.getElementById("rolesContainer").style.display = "block";

        }

        function closeCompanyModal() {

            document.getElementById("companyModal")
                .classList.remove("show");

            document.getElementById("rolesContainer").innerHTML = "";

            document.getElementById("rolesContainer").style.display = "block";

            document.getElementById("roleDetailsContainer").innerHTML = "";

            document.getElementById("roleDetailsContainer").style.display = "none";

        }

        function filterCompanies() {

            let search = document
                .getElementById("companySearch")
                .value
                .toLowerCase();

            let location = document
                .getElementById("locationFilter")
                .value
                .toLowerCase();

            let cards = document.querySelectorAll(".company-card");

            cards.forEach(function(card) {

                let company = card.dataset.company;

                let companyLocation = card.dataset.location;

                let matchCompany = company.includes(search);

                let matchLocation =
                    location === "" ||
                    companyLocation.includes(location);

                if (matchCompany && matchLocation) {

                    card.style.display = "block";

                } else {

                    card.style.display = "none";

                }

            });

        }

        window.onclick = function(event) {

            let modal = document.getElementById("companyModal");

            if (event.target == modal) {

                closeCompanyModal();

            }

        };

        document.addEventListener("keydown", function(event) {

            if (event.key === "Escape") {

                closeCompanyModal();

            }

        });

        function searchCompanies() {

            let input = document
                .getElementById("companySearch")
                .value
                .toLowerCase();

            let cards = document.querySelectorAll(".company-card");

            cards.forEach(function(card) {

                let company = card.dataset.company;

                if (company.includes(input)) {

                    card.style.display = "block";

                } else {

                    card.style.display = "none";

                }

            });

        }
    </script>

</body>

</html>