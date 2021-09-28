<style>
.rescue-container {
    display: flex;
    margin-bottom: 10px;
}

.boot-mode-button {
    border: 1px solid silver;
    cursor: pointer;
    display: grid;
    flex: 1;
    grid-template-columns: 50px 1fr;
    grid-template-rows: auto;
    grid-column-gap: 0px;
    grid-row-gap: 0px;
    margin: 5px;
}

.boot-mode-button--pushed {
    background-color: #ececec;
}

.boot-mode-button-image {
    grid-area: 1 / 1 / 3 / 2;
    text-align: center;
}

.boot-mode-button-title {
    font-size: 17px;
    font-weight: bold;
    grid-area: 1 / 2 / 2 / 3;
}

.boot-mode-button-description {
    grid-area: 2 / 2 / 3 / 3;
}
</style>

<script src="modules/servers/solusiovps/node_modules/chart.js/dist/Chart.js"></script>

<div id="dlg-os-selector" class="modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <p>
                    {$LANG.solusiovps_config_option_operating_system}:
                </p>
                <p>
                    <select id="fld-os-id" class="form-control">
                        <option value="0">.lauris.space</option>
                    </select>
                </p>
                <p style="text-align: right;">
                    <button class="btn btn-danger" onclick="reinstallServerConfirm();">
                        {$LANG.solusiovps_button_reinstall}
                    </button>
                    <button class="btn btn-secondary" onclick="reinstallServerCancel();">
                        {$LANG.solusiovps_button_cancel}
                    </button>
                </p>
            </div>
        </div>
    </div>
</div>

<div id="dlg-rescue-mode" class="modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <p>
                    {$LANG.solusiovps_rescue_mode_summary}
                </p>
                <div class="rescue-container">
                    <div id="btn-boot-mode-disk" class="boot-mode-button boot-mode-button--pushed" onclick="setBootMode('disk');">
                        <div class="boot-mode-button-image">
                            <img src="modules/servers/solusiovps/img/hdd.png" />
                        </div>
                        <div class="boot-mode-button-title">Boot from Disk</div>
                        <div class="boot-mode-button-description">Select this option to boot your server from the disk the next time the server is restarted.</div>
                    </div>
                    <div id="btn-boot-mode-rescue" class="boot-mode-button" onclick="setBootMode('rescue');">
                        <div class="boot-mode-button-image">
                            <img src="modules/servers/solusiovps/img/cd.png" />
                        </div>
                        <div class="boot-mode-button-title">Boot from Rescue ISO</div>
                        <div class="boot-mode-button-description">Select this option to boot your server from the rescue ISO the next time the server is restarted.</div>
                    </div>
                </div>
                <p>
                    {$LANG.solusiovps_rescue_mode_description}
                </p>
                <p style="text-align: right;">
                    <button class="btn btn-info" onclick="rescueModeClose();">
                        {$LANG.solusiovps_button_close}
                    </button>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="media">
    <div class="media-body">
        <div class="panel panel-default">
            <div class="panel-body" style="position: relative">
                <span id="server-status" class="label label-success" style="position: absolute; top: 9px; right: 9px; font-weight: normal; font-size: 12px; text-transform: capitalize; border-radius: 3px; padding-top: 3px">
                    {$data['status']}
                </span>
                <div id="product-actions">
                    <button onclick="startServer();" id="btn-start-server" class="btn btn-info">
                        {$LANG.solusiovps_button_start}
                    </button>
                    <button onclick="stopServer();" id="btn-stop-server" class="btn btn-info">
                        {$LANG.solusiovps_button_stop}
                    </button>
                    <button onclick="restartServer();" id="btn-restart-server" class="btn btn-info">
                        {$LANG.solusiovps_button_restart}
                    </button>
                    <br /><br />
                    <button onclick="reinstallServer();" id="btn-reinstall-server" class="btn btn-info">
                        {$LANG.solusiovps_button_reinstall}
                    </button>
                    <button onclick="openVncDialog();" id="btn-vnc" class="btn btn-info">
                        {$LANG.solusiovps_button_vnc}
                    </button>
                    <button onclick="resetPassword();" id="btn-reset-pw" class="btn btn-info">
                        {$LANG.solusiovps_button_reset_pw}
                    </button>
                    <button onclick="changeHostname();" id="btn-change-hostname" class="btn btn-info">
                        {$LANG.solusiovps_button_change_hostname}
                    </button>
                    <button onclick="rescueMode();" id="btn-rescue-mode" class="btn btn-info">
                        {$LANG.solusiovps_button_rescue_mode}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="display: -webkit-flex; display: flex;">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4>{$LANG.solusiovps_chart_cpu_title}</h4>
                <canvas id="cpuChart" style="height: 200px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row" style="display: -webkit-flex; display: flex;">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4>{$LANG.solusiovps_chart_network_title}</h4>
                <canvas id="networkChart" style="height: 200px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row" style="display: -webkit-flex; display: flex;">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4>{$LANG.solusiovps_chart_disk_title}</h4>
                <canvas id="diskChart" style="height: 200px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row" style="display: -webkit-flex; display: flex;">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4>{$LANG.solusiovps_chart_memory_title}</h4>
                <canvas id="memoryChart" style="height: 200px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row" style="display: -webkit-flex; display: flex;">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-body" style="height: 100%">
                <h4 style="margin:0;padding-bottom:5px">{$LANG.clientareahostingpackage}</h4>
                <hr style="margin: 5px 0;padding-bottom:12px">
                <h4>{$groupname} - {$product}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4 style="margin:0;padding-bottom:5px">{$LANG.orderbillingcycle}</h4>
                <hr style="margin: 5px 0;padding-bottom:12px">
                <h4>{$billingcycle}</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4 style="margin:0;padding-bottom:5px">{$LANG.domainregisternsip}</h4>
                <hr style="margin: 5px 0;padding-bottom:12px">
                <h4>{$data['ip']}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row" style="display: -webkit-flex; display: flex;">
    <div class="col-sm-12">
        <table class="table table-striped">
            <tr>
                <td>{$LANG.clientareastatus}</td>
                <td>
                    {if $status eq 'Active'}
                        <span class="label label-success" style="font-weight: normal;font-size: 12px;border-radius: 3px;padding-top: 3px">{$LANG.clientareaactive}</span>
                    {else}
                        {$status}
                        {if $suspendreason} - {$LANG.suspendreason}: {$suspendreason}{/if}
                    {/if}
                </td>
            </tr>
            <tr>
                <td>{$LANG.clientareahostingregdate}</td>
                <td>{$regdate}</td>
            </tr>
            <tr>
                <td>{$LANG.orderpaymentmethod}</td>
                <td>{$paymentmethod}</td>
            </tr>
            <tr>
                <td>{$LANG.recurringamount}</td>
                <td>{$recurringamount}</td>
            </tr>
            <tr style="border-bottom: 1px solid #dddddd">
                <td>{$LANG.clientareahostingnextduedate}</td>
                <td>{$nextduedate}</td>
            </tr>
        </table>
    </div>
</div>

<div class="row" style="display: -webkit-flex; display: flex;">
    <div class="col-sm-12">
        <table id="tbl_backups" class="table table-striped">
            <thead>
                <tr>
                    <th colspan="3">
                        <a href="javascript:;" onclick="createBackup();" class="btn btn-info">{$LANG.solusiovps_button_create_backup}</a>
                    </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

{if $suspendreason}
    <div class="row">
        <div class="col-sm-5">
            {$LANG.suspendreason}
        </div>
        <div class="col-sm-7">
            {$suspendreason}
        </div>
    </div>
{/if}
<div class="row">
    {if $packagesupgrade}
        <div class="col-sm-4">
            <a href="upgrade.php?type=package&amp;id={$id}" class="btn btn-success btn-block">
                {$LANG.upgrade}
            </a>
        </div>
    {/if}
</div>

<script>
const operatingSystems = {$data['operating_systems']};
const defaultOsId = {$data['default_os_id']};

let domain = '{$data['domain']}';
let bootMode = '{$data['boot_mode']}';

const statusUpdate = status => {
    $('#btn-start-server').prop('disabled', (status !== 'stopped'));
    $('#btn-stop-server').prop('disabled', (status !== 'started'));
    $('#btn-restart-server').prop('disabled', (status !== 'started'));
    $('#btn-reinstall-server').prop('disabled', ((status !== 'stopped') && (status !== 'started')));
    $('#btn-vnc').prop('disabled', (status !== 'started'));
    $('#btn-reset-pw').prop('disabled', (status !== 'started'));
    $('#btn-change-hostname').prop('disabled', ((status !== 'stopped') && (status !== 'started')));
    $('#btn-rescue-mode').prop('disabled', ((status !== 'stopped') && (status !== 'started')));
}

const checkStatus = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/status.php',
        data: {
            serviceId: {$serviceid}
        }
    }).done(function (status) {
        $("#server-status").text(status);

        statusUpdate(status);

        setTimeout(checkStatus, 1000);
    });
}

const startServer = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/start.php',
        data: {
            serviceId: {$serviceid}
        }
    });
}

const stopServer = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/stop.php',
        data: {
            serviceId: {$serviceid}
        }
    });
}

const restartServer = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/restart.php',
        data: {
            serviceId: {$serviceid}
        }
    });
}

const reinstallServer = () => {
    if (!window.confirm('{$LANG.solusiovps_confirm_reinstall}')) {
        return;
    }

    if (Object.keys(operatingSystems).length > 0) {
        let $select = $('#fld-os-id');

        $select.empty();

        for (const [id, name] of Object.entries(operatingSystems)) {
            $select.append($('<option>', {
                value: id,
                text: name
            }));
        }

        $select.val(defaultOsId);

        $('#dlg-os-selector').modal('show');
    } else {
        reinstallServerContinue(defaultOsId);
    }
}

const reinstallServerContinue = osId => {
    $.get({
        url: 'modules/servers/solusiovps/pages/reinstall.php',
        data: {
            serviceId: {$serviceid},
            osId: osId
        }
    });
}

const reinstallServerConfirm = () => {
    const osId = $('#fld-os-id').val();

    reinstallServerContinue(osId);

    $('#dlg-os-selector').modal('hide');
}

const reinstallServerCancel = () => {
    $('#dlg-os-selector').modal('hide');
}

const openVncDialog = () => {
    const width = 800;
    const height = 450;
    const top = (screen.height / 2) - (height / 2);
    const left = (screen.width / 2) - (width / 2);
    const url = 'modules/servers/solusiovps/pages/vnc.php?serviceId={$serviceid}';
    const features = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,width=" + width + ",height=" + height + ",top=" + top + ",left=" + left;

    window.open(url, '', features);
}

const resetPassword = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/reset-password.php',
        data: {
            serviceId: {$serviceid}
        },
        success: function () {
            alert('{$LANG.solusiovps_password_reset_success}');
        }
    });
}

const changeHostname = () => {
    const hostname = prompt('{$LANG.solusiovps_new_hostname}', domain);

    if ((hostname === null) || (hostname === '') || (hostname === domain)) {
        return;
    }

    if (!confirm('{$LANG.solusiovps_confirm_change_hostname}')) {
        return;
    }

    $.get({
        url: 'modules/servers/solusiovps/pages/change-hostname.php',
        data: {
            serviceId: {$serviceid},
            hostname: hostname
        },
        success: function (response) {
            domain = hostname;

            restartServer();

            alert(response);
        }
    });
}

const rescueMode = () => {
    updateBootMode();

    $('#dlg-rescue-mode').modal('show');
}

const rescueModeClose = () => {
    $('#dlg-rescue-mode').modal('hide');
}

const updateBootMode = () => {
    $('.boot-mode-button').removeClass('boot-mode-button--pushed');

    if (bootMode === 'disk') {
        $('#btn-boot-mode-disk').addClass('boot-mode-button--pushed');
    } else {
        $('#btn-boot-mode-rescue').addClass('boot-mode-button--pushed');
    }
}

const setBootMode = mode => {
    if (bootMode === mode) {
        return;
    }

    $.get({
        url: 'modules/servers/solusiovps/pages/change-boot-mode.php',
        data: {
            serviceId: {$serviceid},
            bootMode: mode
        }
    });

    bootMode = mode;

    updateBootMode();
}

const getBackups = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/get-backups.php',
        data: {
            serviceId: {$serviceid}
        },
        dataType: 'json'
    }).done(function (backups) {
        let $tbody = $("#tbl_backups > tbody");

        $tbody.empty();

        backups.forEach(function (backup) {
            let restore = '';

            if (backup.status === 'created') {
                restore = '<a href="javascript:;" onclick="restoreBackup(' + backup.id + ');">{$LANG.solusiovps_button_restore_backup}</a>';
            }

            let html = '<tr>';
            html += '<td>' + backup.time + '</td>';
            html += '<td>' + backup.status + '</td>';
            html += '<td>' + backup.message + '</td>';
            html += '<td>' + restore + '</td>';
            html += '</tr>';

            $tbody.append(html);
        });

        setTimeout(getBackups, 3000);
    });
}

const createBackup = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/create-backup.php',
        data: {
            serviceId: {$serviceid}
        }
    });
}

const restoreBackup = backupId => {
    $.get({
        url: 'modules/servers/solusiovps/pages/restore-backup.php',
        data: {
            serviceId: {$serviceid},
            backupId: backupId
        }
    });
}

const getUsage = () => {
    $.get({
        url: 'modules/servers/solusiovps/pages/usage.php',
        data: {
            serviceId: {$serviceid}
        },
        dataType: 'json'
    }).done(function (usage) {
        cpuChartData.labels = [];
        cpuChartData.datasets[0].data = [];

        usage.cpu.forEach(item => {
            cpuChartData.labels.push(item.second);
            cpuChartData.datasets[0].data.push(item.load_average);
        });

        cpuChart.update();

        networkChartData.labels = [];
        networkChartData.datasets[0].data = [];
        networkChartData.datasets[1].data = [];

        usage.network.forEach(item => {
            networkChartData.labels.push(item.second);
            networkChartData.datasets[0].data.push(item.read_kb);
            networkChartData.datasets[0].data.push(item.write_kb);
        });

        networkChart.update();

        diskChartData.labels = [];
        diskChartData.datasets[0].data = [];
        diskChartData.datasets[1].data = [];

        usage.disk.forEach(item => {
            diskChartData.labels.push(item.second);
            diskChartData.datasets[0].data.push(item.read_kb);
            diskChartData.datasets[0].data.push(item.write_kb);
        });

        diskChart.update();

        memoryChartData.labels = [];
        memoryChartData.datasets[0].data = [];

        usage.memory.forEach(item => {
            memoryChartData.labels.push(item.second);
            memoryChartData.datasets[0].data.push(item.memory);
        });

        memoryChart.update();

        setTimeout(getUsage, 5000);
    });
}

const cpuChartData = {
    labels: [],
    datasets: [{
        label: '{$LANG.solusiovps_chart_cpu_label_load}',
        data: [],
        fill: true,
        backgroundColor: 'rgba(138,173,65,0.5)',
        borderColor: 'rgba(138,173,65,1)',
        borderWidth: 2,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        pointRadius: 1,
        pointHitRadius: 10,
        pointHoverBackgroundColor: 'rgba(138,173,65,1)',
        pointHoverBorderColor: 'rgba(138,173,65,0.5)'
    }]
};

const cpuChart = new Chart($('#cpuChart'), {
    type: 'line',
    data: cpuChartData,
    options: {
        animation: false,
        responsive: false,
        scales: {
            xAxes: [{
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 20
                }
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    min: 0,
                    precision: 0
                }
            }]
        }
    }
});

const networkChartData = {
    labels: [],
    datasets: [{
        label: '{$LANG.solusiovps_chart_network_label_read}',
        data: [],
        fill: true,
        backgroundColor: 'rgba(40,170,222,0.5)',
        borderColor: 'rgba(40,170,222,1)',
        borderWidth: 2,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        pointRadius: 1,
        pointHitRadius: 10,
        pointHoverBackgroundColor: 'rgba(40,170,222,1)',
        pointHoverBorderColor: 'rgba(40,170,222,0.5)'
    },{
        label: '{$LANG.solusiovps_chart_network_label_write}',
        data: [],
        fill: true,
        backgroundColor: 'rgba(138,173,65,0.5)',
        borderColor: 'rgba(138,173,65,1)',
        borderWidth: 2,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        pointRadius: 1,
        pointHitRadius: 10,
        pointHoverBackgroundColor: 'rgba(138,173,65,1)',
        pointHoverBorderColor: 'rgba(138,173,65,0.5)'
    }]
};

const networkChart = new Chart($('#networkChart'), {
    type: 'line',
    data: networkChartData,
    options: {
        animation: false,
        responsive: false,
        scales: {
            xAxes: [{
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 20
                }
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    min: 0,
                    precision: 0
                }
            }]
        }
    }
});

const diskChartData = {
    labels: [],
    datasets: [{
        label: '{$LANG.solusiovps_chart_disk_label_read}',
        data: [],
        fill: true,
        backgroundColor: 'rgba(40,170,222,0.5)',
        borderColor: 'rgba(40,170,222,1)',
        borderWidth: 2,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        pointRadius: 1,
        pointHitRadius: 10,
        pointHoverBackgroundColor: 'rgba(40,170,222,1)',
        pointHoverBorderColor: 'rgba(40,170,222,0.5)'
    },{
        label: '{$LANG.solusiovps_chart_disk_label_write}',
        data: [],
        fill: true,
        backgroundColor: 'rgba(138,173,65,0.5)',
        borderColor: 'rgba(138,173,65,1)',
        borderWidth: 2,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        pointRadius: 1,
        pointHitRadius: 10,
        pointHoverBackgroundColor: 'rgba(138,173,65,1)',
        pointHoverBorderColor: 'rgba(138,173,65,0.5)'
    }]
};

const diskChart = new Chart($('#diskChart'), {
    type: 'line',
    data: diskChartData,
    options: {
        animation: false,
        responsive: false,
        scales: {
            xAxes: [{
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 20
                }
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    min: 0,
                    precision: 0
                }
            }]
        }
    }
});

const memoryChartData = {
    labels: [],
    datasets: [{
        label: '{$LANG.solusiovps_chart_memory_label_usage}',
        data: [],
        fill: true,
        backgroundColor: 'rgba(138,173,65,0.5)',
        borderColor: 'rgba(138,173,65,1)',
        borderWidth: 2,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        pointRadius: 1,
        pointHitRadius: 10,
        pointHoverBackgroundColor: 'rgba(138,173,65,1)',
        pointHoverBorderColor: 'rgba(138,173,65,0.5)'
    }]
};

const memoryChart = new Chart($('#memoryChart'), {
    type: 'line',
    data: memoryChartData,
    options: {
        animation: false,
        responsive: false,
        scales: {
            xAxes: [{
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 20
                }
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    min: 0,
                    precision: 0
                }
            }]
        }
    }
});

statusUpdate('{$data['status']}');
checkStatus();
getUsage();
getBackups();
</script>
