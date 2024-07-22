<!DOCTYPE html>
<html>

<?php include 'includes/interface_assets/header.php'; ?>

<body>
    <div class="container" style="max-width: 1200px; margin-top: 8rem; ">
        <div class="card mt-4" style="min-height: 750px; margin: 0 auto;">
            <div class="card-body text-center">
                <h3 style="margin-top: 150px;"><?= __("Installation"); ?></h3>

                <p style="margin-bottom: 60px;"><?= __("Please wait..."); ?></p>

                <div class="mb-3" id="config_file" style="opacity: 50%;">
                    <i id="config_file_spinner" class="ld-ext-right"><?= __("Copy config.php to application/config/") ?><div class="ld ld-ring ld-spin"></div></i><i id="config_file_check" class="ms-2 fas fa-check-circle" style="display: none;"></i>
                </div>
                <div class="mb-3" id="database_file" style="opacity: 50%;">
                    <i id="database_file_spinner" class="ld-ext-right"><?= __("Copy database.php to application/config/") ?><div class="ld ld-ring ld-spin"></div></i><i id="database_file_check" class="ms-2 fas fa-check-circle" style="display: none;"></i>
                </div>
                <div class="mb-3" id="database_tables" style="opacity: 50%;">
                    <i id="database_tables_spinner" class="ld-ext-right"><?= __("Creating database tables") ?><div class="ld ld-ring ld-spin"></div></i><i id="database_tables_check" class="ms-2 fas fa-check-circle" style="display: none;"></i>
                </div>
                <div class="mb-3" id="database_migrations" style="opacity: 50%;">
                    <i id="database_migrations_spinner" class="ld-ext-right"><?= __("Running database migrations") ?><div class="ld ld-ring ld-spin"></div></i><i id="database_migrations_check" class="ms-2 fas fa-check-circle" style="display: none;"></i>
                </div>
                <div class="mb-3" id="update_dxcc" style="opacity: 50%;">
                    <i id="update_dxcc_spinner" class="ld-ext-right"><?= __("Updating DXCC data") ?><i id="skip_dxcc_update_message"></i><div class="ld ld-ring ld-spin"></div></i><i id="update_dxcc_check" class="ms-2 fas fa-check-circle" style="display: none;"></i>
                </div>

                <?php
                // we can easily add more steps here if necessary 
                ?>

                <div class="mb-3" id="installer_lock" style="opacity: 50%;">
                    <i id="installer_lock_spinner" class="ld-ext-right"><?= __("Lock the installer") ?><div class="ld ld-ring ld-spin"></div></i><i id="installer_lock_check" class="ms-2 fas fa-check-circle" style="display: none;"></i>
                </div>

                <div class="mb-3" id="success_message" style="display: none;">
                    <p><?= sprintf(__("All install steps went through. Redirect to user login in %s seconds..."), "<span id='countdown'>4</span>"); ?></p>
                </div>
                <div class="mb-3" id="success_button" style="display: none;">
                    <a class="btn btn-primary" href="<?php echo $_POST['websiteurl'] ?? $websiteurl."index.php/user/login/1"; ?>"><?= __("Done. Go to the user login ->"); ?></a>
                </div>
                <div id="error_message"></div>
                <div class="container mt-5">
                    <button id="toggleLogButton" class="btn btn-sm btn-secondary mb-3"><?= __("Show detailled debug log"); ?></button>
                    <div id="logContainer">
                        <pre>
                            <code id="debuglog">
                                <!-- Log Content -->
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>

    let _POST = <?php echo json_encode($_POST); ?>;

    $(document).ready(async function() {
        init_read_log();
        try {
            await check_lockfile();

            await config_file();
            await database_file();
            await database_tables();
            await database_migrations();
            await update_dxcc();
            await installer_lock();

            log_message('info', 'Finish. Installer went through successfully.');

            if($('#logContainer').css('display') == 'none') {
                // after all install steps went through we can show a success message and redirect to the user/login
                $("#success_message").show();

                // Initialize the countdown
                var countdown = 4;
                var countdownInterval = setInterval(function() {
                    countdown--;
                    $("#countdown").text(countdown);
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = _POST.websiteurl + "index.php/user/login/1";
                    }
                }, 1000);
            } else {
                // after all install steps went through we can show the redirect button
                $("#success_button").show();
            }
        } catch (error) {
            $("#error_message").text("Installation failed: " + error).show();
        }
    });

    function init_read_log() {
        setInterval(function() {
            $.ajax({
                type: 'POST',
                    url: 'ajax.php',
                    data: {
                        read_logfile: 1
                    },
                success: function(response) {
                    $("#debuglog").text(response);
                }
            });
        }, 500);
    }

    $('#toggleLogButton').on('click', function() {
        var logContainer = $('#logContainer');
        logContainer.toggle();
        console.log(logContainer.css('display'));
        if(logContainer.css('display') == 'none') {
            $('#toggleLogButton').text("<?= __("Show detailled debug log"); ?>");
        } else {
            $('#toggleLogButton').text("<?= __("Hide detailled debug log"); ?>");
        }
    });

    // if a user goes back to the installer we need to redirect him
    async function check_lockfile() {
        
        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    check_lockfile: 1
                },
                success: function(response) {
                    if (response != 'installer_locked') {
                        resolve();
                    } else {
                        log_message('error', 'Attention: Installer is locked. Redirect to user/login.');
                        reject(response);
                        window.location.href = "<?php echo str_replace('run.php', '', $websiteurl); ?>" + "index.php/user/login";
                    }
                },
                error: function(error) {
                    log_message('error', "Install Lock Check went wrong...");
                    reject(error);
                    window.location.href = "<?php echo str_replace('run.php', '', $websiteurl); ?>" + "index.php/user/login";
                }
            });
        });
    }

    async function config_file() {

        var field = '#config_file';

        running(field, true);
        log_message('debug', 'Start writing config.php');

        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    data: _POST,
                    run_config_file: 1
                },
                success: function(response) {
                    if (response == 'success') {
                        running(field, false);
                        log_message('debug', 'File: config.php successfully written');
                        resolve();
                    } else {
                        running(field, false, true);
                        log_message('error', 'File: Could not write file. Check Permissions.');
                        reject("<?= __("Could not create application/config/config.php"); ?>");
                    }
                },
                error: function(error) {
                    log_message('error', 'File: Could not write file. Ajax failed.');
                    running(field, false, true);
                    reject(error);
                }
            });
        });
    }

    async function database_file() {

        var field = '#database_file';

        running(field, true);
        log_message('debug', 'Start writing database.php');

        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    data: _POST,
                    run_database_file: 1
                },
                success: function(response) {
                    if (response == 'success') {
                        running(field, false);
                        log_message('debug', 'File: database.php successfully written');
                        resolve();
                    } else {
                        running(field, false, true);
                        log_message('error', 'File: Could not write file. Check Permissions.');
                        reject("<?= __("Could not create application/config/database.php"); ?>");
                    }
                },
                error: function(error) {
                    log_message('error', 'File: Could not write file. Ajax failed.');
                    running(field, false, true);
                    reject(error);
                }
            });
        });
    }

    async function database_tables() {
        var field = '#database_tables';

        running(field, true);
        log_message('debug', 'Start creating database structure with assets/install.sql');

        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    data: _POST,
                    run_database_tables: 1
                },
                success: function(response) {
                    if (response == 'success') {
                        log_message('debug', 'Tables successfully created');
                        running(field, false);
                        resolve();
                    } else {
                        running(field, false, true);
                        log_message('error', 'Creating database tables from assets/install.sql failed.');
                        reject("<?= __("Could not create database tables"); ?>");
                    }
                },
                error: function(error) {
                    running(field, false, true);
                    log_message('error', 'Creating database tables failed. Ajax crashed.');
                    reject(error);
                }
            });
        });
    }

    async function database_migrations() {
        var field = '#database_migrations';

        running(field, true);
        log_message('debug', 'Start migrating database to the newest version.');

        return new Promise((resolve, reject) => {
            $.ajax({
                url: "<?php echo $_POST['websiteurl'] ?? $websiteurl; ?>" + "index.php/migrate",
                success: function(response) {
                    if (response == 'success') {
                        running(field, false);
                        log_message('debug', 'Database successfully created.');
                        resolve();
                    } else {
                        running(field, false, true);
                        log_message('error', 'Could not migrate database.');
                        reject("<?= __("Could not run database migrations"); ?>");
                    }
                },
                error: function(error) {
                    running(field, false, true);
                    log_message('error', 'Could not migrate database. Ajax crashed.');
                    reject(error);
                }
            });
        });
    }

    async function update_dxcc() {
        var field = '#update_dxcc';

        return new Promise((resolve, reject) => {
            if(_POST.skip_dxcc_update == 0) {

                running(field, true);
                log_message('debug', 'Start updating DXCC database. This can take a moment or two... Please wait');

                $.ajax({
                    url: "<?php echo $_POST['websiteurl'] ?? $websiteurl; ?>" + "index.php/update/dxcc",
                    success: function(response) {
                        if (response == 'success') {
                            running(field, false);
                            log_message('debug', 'Successfully update DXCC database');
                            resolve();
                        } else {
                            running(field, false, true);
                            log_message('error', 'Could not update DXCC data.');
                            reject("<?= __("Could not update DXCC data"); ?>");
                        }
                    },
                    error: function(error) {
                        running(field, false, true);
                        log_message('error', 'Could not update DXCC data. Ajax crashed.');
                        reject(error);
                    }
                });
            } else {
                $('#skip_dxcc_update_message').text(" "+"<?= __("(skipped)"); ?>");
                resolve();
            }
        });
    }

    async function installer_lock() {
        var field = '#installer_lock';
        log_message('debug', 'Try to create .lock file for the installer');

        running(field, true);
        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    run_installer_lock: 1
                },
                success: function(response) {
                    if (response == 'success') {
                        running(field, false);
                        log_message('debug', 'Successfully created .lock file in folder /install');
                        resolve();
                    } else {
                        running(field, false, true);
                        log_message('error', 'Could not create .lock file.');
                        reject("<?= __("Could not create install/.lock file"); ?>");
                    }
                },
                error: function(error) {
                    running(field, false, true);
                    log_message('error', 'Could not create .lock file. Ajax crashed');
                    reject(error);
                }
            });
        });
    }

    //

    function running(field, running, failure = false) {

        if (running) {
            $(field).css('opacity', '100%');
            $(field + '_spinner').addClass("running");
        } else {
            $(field + '_spinner').removeClass("running");
            if (failure) {
                $(field + '_check').addClass('fa-times-circle');
                $(field + '_check').css('color', 'red');
            } else {
                $(field + '_check').addClass('fa-check-circle');
                $(field + '_check').css('color', '#04a004');
            }
            $(field + '_check').css('display', 'inline');
        }

    }
</script>

<?php include 'includes/interface_assets/footer.php'; ?>

</html>