jQuery(document).ready(function($){

	// Initialize the bandwidth doughnut chart
	var bwChartID = $("#cmd-acf-bwchart").get(0).getContext("2d");
	var bwChart = new Chart(bwChartID).Doughnut(cmd_afc_bandwidth, {
		animation: false,
		tooltipTemplate : function(data) {
			return data.label + ': ' + get_bw_formatted_value(data);
		} 
	} );

	// Initialize the ssl doughnut chart
	var sslChartID = $("#cmd-acf-sslchart").get(0).getContext("2d");
	var sslChart = new Chart(sslChartID).Doughnut(cmd_afc_ssl, {
		animation: false
	} );

	// Initialize the content type pie chart
	var ctChartID = $("#cmd-acf-ctchart").get(0).getContext("2d");
	var ctChart = new Chart(ctChartID).Pie(cmd_afc_content_types, {
		animation: false
	} );

	// Initialize the request by country pie chart
	var rcChartID = $("#cmd-acf-rcchart").get(0).getContext("2d");
	var rcChart = new Chart(rcChartID).Pie(cmd_afc_countries, {
		animation: false
	} );


	// Custom line chart definition
	Chart.types.Line.extend({
		name : "AltLine",

		initialize: function (data) {
			// If there are more than 7 datapoints then skip the label for every other one
			Chart.types.Line.prototype.initialize.apply(this, arguments);
			var xLabels = this.scale.xLabels;
			if ( xLabels.length > 7 ) {
				xLabels.forEach(function ( label, i ) {
					if ( 1 === i % 2 )
						xLabels[i] = '';
				});
			}
		}
	});

	// Initialize the main line chart
	var inChartID = $("#cmd-acf-linechart").get(0).getContext("2d");
	var inChart = new Chart(inChartID).AltLine(cmd_afc_interval, {
		animation: false,
		maintainAspectRatio: false,
		scaleBeginAtZero: true,
		bezierCurve : false,
		scaleLabel: function(data) {	
				// Format the bandwidth label to MB
				if ( 'bandwidth' === cmd_afc_current_type ) {
					return ( ( data.value/1024 )/1024 ).toFixed(1) + 'MB';
				} else {
					return data.value;
				}
		},
		multiTooltipTemplate : function(data) {
				// Format the bandwidth tooltip to MB
				if ( 'bandwidth' === cmd_afc_current_type ) {
					return data.datasetLabel + ': ' + bytesToSize( data.value );
				} else {
					return data.datasetLabel + ': ' + data.value;
				}
		} 
	});

	// Create the chart legend
	document.getElementById('cmd-acf-js-legend').innerHTML = inChart.generateLegend();

	// Grab the value of the display label to show
	function get_bw_formatted_value(data){
		var returnval = '';
		$.each(cmd_afc_bandwidth, function( i, item ) {
			if ( item.label == data.label ) {
				returnval = item.display;
			}
		});
	 	return returnval;
	}

	// Convert bytes to a more precice display
	function bytesToSize(bytes) {
		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		if ( 0 === bytes ) {
			return '0 Byte';
		}
		console.log(bytes);
		var i = parseInt( Math.floor( Math.log(bytes) / Math.log(1024) ) );
		return ( bytes / Math.pow(1024, i) ).toFixed(2) + ' ' + sizes[i];
	}


});