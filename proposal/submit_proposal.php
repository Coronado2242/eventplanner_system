<?php
session_start();
session_unset(); // Clear all session data on final submission

// Here you can add code to update proposal status or notify admin

echo "<script>
    alert('Proposal submitted successfully.');
    window.location.href = 'proposal.php';
</script>";
