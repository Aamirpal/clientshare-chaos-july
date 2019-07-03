$(document).on('click', '.report_list-li', function() {
    $('#reportSubContainer').remove();
    credentials = JSON.parse($(this).attr('data-get-report'));

    switch($(this).attr('data-report-type')){
        case 'report':
            getReportToken(credentials);
            break;
        case 'dashboard':
            triggerDashboardReport(credentials);
            break;
    }
});

function getReportToken(credentials) {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        async: false,
        url: credentials['Function URL']+'&reportId='+credentials['Report ID'],
        success: function(response){
            triggerReport(response);
        }
    });
}

function triggerDashboardReport(credentials){
    var accessToken = credentials['Embed Token'];
    var embedUrl = credentials['Embed URL'];
    var embedReportId = credentials['Dashboard ID'];
    var models = window['powerbi-client'].models;
    var config = {
        type: 'dashboard',
        tokenType: models.TokenType.Embed,
        accessToken: accessToken,
        embedUrl: embedUrl,
        id: embedReportId,
        permissions: models.Permissions.All
    };
    $('#reportContainer').append('<div style="height: 100%" id="reportSubContainer"></div>');
    var reportContainer = $('#reportSubContainer')[0];
    var report = powerbi.embed(reportContainer, config);
    report.reload().catch(error => {});
}

function triggerReport(credentials) {
    var accessToken = credentials['embedToken'];
    var embedUrl = "https://app.powerbi.com/reportEmbed?reportId="+credentials['reportId']+"&groupId="+credentials['groupId'];
    var embedReportId = credentials['reportId'];
    var models = window['powerbi-client'].models;
    var config = {
        type: 'report',
        tokenType: models.TokenType.Embed,
        accessToken: accessToken,
        embedUrl: embedUrl,
        id: embedReportId,
        permissions: models.Permissions.All,
        settings: {
            filterPaneEnabled: true,
            navContentPaneEnabled: true
        }
    };
    $('#reportContainer').append('<div style="height: 100%" id="reportSubContainer"></div>');
    var reportContainer = $('#reportSubContainer')[0];
    var report = powerbi.embed(reportContainer, config);
    report.reload().catch(error => {});
}

$(document).ready(function(){
    $('ul.report-list li').eq(0).trigger('click').find('a').addClass('active');
});