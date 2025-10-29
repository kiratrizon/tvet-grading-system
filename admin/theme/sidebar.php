<aside class="sidebar">
    <nav class="nav-sidebar">
        <ul class="navigation">
            <li><a href="index.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="teacher.php"><i class="fa-solid fa-chalkboard-teacher"></i>Instructor</a></li>
            <li><a href="asignteacher.php"><i class="fa-solid fa-user-tag"></i> Assign to Instructor</a></li>
            <li class="has-submenu">
                <a href="#" class="submenu-toggle">
                    <div>
                        <i class="fa-solid fa-book"></i>
                        <span style="padding-left: 12px;"> Academics</span>
                    </div>
                    <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                    <li><a href="subjects.php"><i class="fa-solid fa-book"></i> Subjects</a></li>
                    <li><a href="courses.php"><i class="fa-solid fa-graduation-cap"></i> Programs</a></li>
                </ul>
            </li>
            <li class="has-submenu">
                <a href="#" class="submenu-toggle">
                    <div>
                        <i class="fa-solid fa-user-graduate"></i>
                        <span style="padding-left: 12px;"> Students</span>
                    </div>
                    <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                    <li><a href="students.php"><i class="fa-solid fa-calculator"></i> Student Grades</a></li>
                    <li><a href="students_add.php"><i class="fa-solid fa-user-plus"></i> Add Student</a></li>
                </ul>
            </li>
            <li><a href="settings.php"><i class="fa-solid fa-cogs"></i> Settings</a></li>
        </ul>
        <ul class="navigation">
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a></li>
        </ul>
    </nav>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let submenuToggles = document.querySelectorAll(".submenu-toggle");

        submenuToggles.forEach(toggle => {
            toggle.addEventListener("click", function(e) {
                e.preventDefault();
                let parent = this.parentElement;
                parent.classList.toggle("active");
            });
        });
    });
</script>

<style>
    .has-submenu .submenu {
        display: none;
        list-style: none;
        padding-left: 20px;
    }

    .has-submenu.active .submenu {
        display: block;
    }

    .submenu-toggle {
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .submenu-toggle i:last-child {
        transition: transform 0.3s ease;
    }

    .has-submenu.active .submenu-toggle i:last-child {
        transform: rotate(180deg);
    }
</style>