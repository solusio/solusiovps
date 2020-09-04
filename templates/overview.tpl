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
                    <button onclick="reinstallServer();" id="btn-reinstall-server" class="btn btn-info">
                        {$LANG.solusiovps_button_reinstall}
                    </button>
                    <button onclick="openVncDialog();" id="btn-vnc" class="btn btn-info">
                        {$LANG.solusiovps_button_vnc}
                    </button>
                    <button onclick="resetPassword();" id="btn-reset-pw" class="btn btn-info">
                        {$LANG.solusiovps_button_reset_pw}
                    </button>
                </div>
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

const statusUpdate = status => {
    $('#btn-start-server').prop('disabled', (status !== 'stopped'));
    $('#btn-stop-server').prop('disabled', (status !== 'started'));
    $('#btn-restart-server').prop('disabled', (status !== 'started'));
    $('#btn-reinstall-server').prop('disabled', ((status !== 'stopped') && (status !== 'started')));
    $('#btn-vnc').prop('disabled', (status !== 'started'));
    $('#btn-reset-pw').prop('disabled', (status !== 'started'));
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

statusUpdate('{$data['status']}');
checkStatus();
getBackups();
</script>
