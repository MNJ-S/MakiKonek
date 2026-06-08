<?php
require __DIR__ . '/../includes/db_connect.php';

$archive_query = "
    INSERT INTO archived_users (original_user_id, username, email, role, archived_reason)
    SELECT user_id, username, email, role, 'Suspended' FROM users LIMIT 1";

if(!mysqli_query($conn, $archive_query)) { 
    echo "users err: " . mysqli_error($conn) . "\n"; 
} else { 
    echo "users success\n"; 
}

$archive_profile_query = "
    INSERT INTO archived_user_profiles (
        original_user_id, first_name, last_name, middle_name, suffix, avatar_path,
        sex, civil_status, birth_date, birth_place, religion, nationality,
        mobile_number, house_no, street, purok_no, subdivision, years_residency,
        employed_status, date_registration
    )
    SELECT
        user_id, first_name, last_name, middle_name, suffix, avatar_path,
        sex, civil_status, birth_date, birth_place, religion, nationality,
        mobile_number, house_no, street, purok_no, subdivision, years_residency,
        employed_status, date_registration
    FROM user_profiles LIMIT 1";

if(!mysqli_query($conn, $archive_profile_query)) { 
    echo "profiles err: " . mysqli_error($conn) . "\n"; 
} else { 
    echo "profiles success\n"; 
}
