<?php
$title = "500 Internal Server Error";
?>
<!DOCTYPE html>
<html lang="en">
    <?php include('includes/head.php') ?>
    <body>
        <main>
            <section class="terms-condition">
                <div class="container text-center">
                    <div class="sder-0">
                        <div class="card-2">
                            <h1 class="display-4 text-danger fw-bold">500</h1>
                            <h2 class="text-dark">Internal Server Error</h2>
                            <p class="text-muted mt-2">Oops! Something went wrong on our end. Please try again later.</p>
                            <button class="btn btn-danger mt-4" onclick="location.reload()">Retry</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
    <?php include('includes/footer-scripts.php') ?>
</html>
