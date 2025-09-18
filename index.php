<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year_level = intval($_POST['year_level'] ?? 0);

    if ($name === '' || $email === '' || $course === '' || $year_level < 1 || $year_level > 5) {
        $message = 'Please fill all fields correctly.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } else {
        try {
            if ($id > 0) {
                
                $stmt = $pdo->prepare("UPDATE students SET name = :name, email = :email, course = :course, year_level = :year_level WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':course' => $course,
                    ':year_level' => $year_level,
                    ':id' => $id
                ]);
                $message = "Student updated successfully.";
            } else {
                
                $stmt = $pdo->prepare("INSERT INTO students (name, email, course, year_level) VALUES (:name, :email, :course, :year_level)");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':course' => $course,
                    ':year_level' => $year_level
                ]);
                $message = "Student registered successfully.";
            }
            
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Email already registered.';
            } else {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}


if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$delete_id]);
        $message = "Student deleted successfully.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}


try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Failed to fetch students: ' . $e->getMessage();
}


$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    if ($edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_student = $stmt->fetch();
        if (!$edit_student) {
            $message = 'Student not found for editing.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Student Registration System - CRUD</title>
<link rel="stylesheet" href="Registration.css">
<style>
        
    *, *::before, *::after {
        box-sizing: border-box;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f7fa;
        margin: 0;
        padding: 0;
        color: #2c3e50;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px 15px;
    }
    header {
        width: 100%;
        max-width: 900px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 10px;
        border-bottom: 2px solid #2980b9;
    }
    header .welcome {
        font-weight: 600;
        font-size: 1.1rem;
        color: #2980b9;
    }
    header a.logout {
        text-decoration: none;
        background: #e74c3c;
        color: white;
        padding: 10px 20px;
        border-radius: 30px;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(231, 76, 60, 0.4);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    header a.logout:hover {
        background: #c0392b;
        box-shadow: 0 6px 12px rgba(192, 57, 43, 0.6);
    }
    h1 {
        max-width: 900px;
        margin: 0 0 30px 0;
        font-weight: 700;
        font-size: 2rem;
        color: #34495e;
        text-align: center;
        user-select: none;
    }
    form {
        background: white;
        max-width: 900px;
        width: 100%;
        border-radius: 12px;
        padding: 30px 40px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        margin-bottom: 40px;
        transition: box-shadow 0.3s ease;
    }
    form:hover {
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }
    label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95rem;
        color: #34495e;
    }
    input[type="text"],
    input[type="email"],
    select {
        width: 100%;
        padding: 14px 15px;
        font-size: 1rem;
        border: 2px solid #ccc;
        border-radius: 8px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 20px;
        font-family: inherit;
        color: #2c3e50;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    select:focus {
        border-color: #2980b9;
        outline: none;
        box-shadow: 0 0 8px #2980b9aa;
    }
    button {
        background-color: #2980b9;
        color: white;
        border: none;
        padding: 15px 0;
        font-size: 1.1rem;
        border-radius: 30px;
        width: 100%;
        cursor: pointer;
        font-weight: 700;
        letter-spacing: 1px;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
    }
    button:hover {
        background-color: #1c5980;
        box-shadow: 0 6px 15px rgba(28, 89, 128, 0.5);
    }
    form a.cancel-edit {
        display: inline-block;
        margin-top: 12px;
        color: #2980b9;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
        user-select: none;
    }
    form a.cancel-edit:hover {
        color: #1c5980;
        text-decoration: underline;
    }
    .message {
        max-width: 900px;
        width: 100%;
        margin-bottom: 30px;
        padding: 15px 25px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        user-select: none;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .success {
        background-color: #2ecc71;
        color: white;
        box-shadow: 0 3px 12px #27ae601a;
    }
    .error {
        background-color: #e74c3c;
        color: white;
        box-shadow: 0 3px 12px #c0392b1a;
    }
    h2 {
        max-width: 900px;
        width: 100%;
        margin-bottom: 20px;
        font-weight: 700;
        font-size: 1.8rem;
        color: #34495e;
        user-select: none;
    }
    table {
        max-width: 900px;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
        font-size: 1rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        border-radius: 12px;
        overflow: hidden;
        background: white;
        user-select: none;
    }
    thead tr {
        background-color: #2980b9;
        color: white;
        font-weight: 700;
        font-size: 1rem;
    }
    thead th {
        padding: 15px 20px;
        text-align: left;
    }
    tbody tr {
        background: #fff;
        box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        transition: transform 0.2s ease;
        cursor: default;
        border-radius: 10px;
    }
    tbody tr:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    tbody td {
        padding: 15px 20px;
        vertical-align: middle;
        color: #34495e;
    }
    tbody td:first-child {
        font-weight: 600;
        color: #2980b9;
    }
    tbody td.actions {
        white-space: nowrap;
    }
    a.button-link {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        color: white;
        margin-right: 10px;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
    }
    a.edit-btn {
        background-color: #27ae60;
        box-shadow: 0 4px 8px #27ae6040;
    }
    a.edit-btn:hover {
        background-color: #1e8449;
        box-shadow: 0 6px 14px #1e844940;
    }
    a.delete-btn {
        background-color: #e74c3c;
        box-shadow: 0 4px 8px #e74c3c40;
    }
    a.delete-btn:hover {
        background-color: #c0392b;
        box-shadow: 0 6px 14px #c0392b40;
    }

    
    @media (max-width: 720px) {
        body {
            padding: 20px 10px;
        }
        form, header, table {
            max-width: 100%;
        }
        thead {
            display: none;
        }
        tbody tr {
            display: block;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            border-radius: 12px;
        }
        tbody td {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }
        tbody td:last-child {
            border-bottom: none;
        }
        tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #2980b9;
            flex: 1;
        }
        tbody td.actions {
            justify-content: flex-start;
        }
        a.button-link {
            margin-right: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
        }
    }
</style>
</head>
<body>
<header>
    <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
    <a href="logout.php" class="logout">Logout</a>
</header>

<h1>Student Registration</h1>

<?php if ($message): ?>
    <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>


<form id="studentForm" method="POST" action="">
    <input type="hidden" name="student_id" value="<?php echo $edit_student ? (int)$edit_student['id'] : '0'; ?>" />

    <label for="name">Name <span style="color:#e74c3c;">*</span></label>
    <input type="text" id="name" name="name" placeholder="Enter full name" required
           value="<?php echo $edit_student ? htmlspecialchars($edit_student['name']) : ''; ?>" />

    <label for="email">Email <span style="color:#e74c3c;">*</span></label>
    <input type="email" id="email" name="email" placeholder="Enter email address" required
           value="<?php echo $edit_student ? htmlspecialchars($edit_student['email']) : ''; ?>" />

    <label for="course">Course <span style="color:#e74c3c;">*</span></label>
    <input type="text" id="course" name="course" placeholder="Enter course name" required
           value="<?php echo $edit_student ? htmlspecialchars($edit_student['course']) : ''; ?>" />

    <label for="year_level">Year Level <span style="color:#e74c3c;">*</span></label>
    <select id="year_level" name="year_level" required>
        <option value="">-- Select Year Level --</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?php echo $i; ?>" <?php
                echo ($edit_student && (int)$edit_student['year_level'] === $i) ? 'selected' : '';
            ?>><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>

    <button type="submit"><?php echo $edit_student ? 'Update Student' : 'Register Student'; ?></button>
    <?php if ($edit_student): ?>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline-block; margin-top:10px; color:#2980b9;">Cancel Editing</a>
    <?php endif; ?>
</form>

<h2>Registered Students</h2>
<?php if (count($students) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                    <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                    <td>
                        <a href="?edit=<?php echo (int)$student['id']; ?>" class="button-link edit-btn">Edit</a>
                        <a href="?delete=<?php echo (int)$student['id']; ?>" 
                           class="button-link delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No registered students yet.</p>
<?php endif; ?>

<script>
    
    document.getElementById('studentForm').addEventListener('submit', function(event) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const course = document.getElementById('course').value.trim();
        const year_level = document.getElementById('year_level').value;

        if (!name || !email || !course || !year_level) {
            alert('Please fill in all required fields.');
            event.preventDefault();
            return;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert('Please enter a valid email address.');
            event.preventDefault();
            return;
        }

        const yearNum = parseInt(year_level, 10);
        if (yearNum < 1 || yearNum > 5) {
            alert('Year level must be between 1 and 5.');
            event.preventDefault();
        }
    });
</script>
</body>
</html>