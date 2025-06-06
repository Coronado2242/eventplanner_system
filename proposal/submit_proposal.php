<?php
session_start();
session_unset(); 

echo "<script>
    alert('Proposal submitted successfully.');
    window.location.href = 'proposal.php';
</script>";
