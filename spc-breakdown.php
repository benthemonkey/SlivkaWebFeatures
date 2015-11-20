<style type="text/css">
	.breakdown {
		font-size: 12px;
	}
	.chart {
		height: 250px;
	}

	td {
			transition-duration: 0.5s;
	-webkit-transition-duration: 0.5s;
	   -moz-transition-duration: 0.5s;
		 -o-transition-duration: 0.5s;
	}
</style>
<div class="row">
	<div class="form-group col-md-3">
		<label for="slivkan" class="control-label">Slivkan:</label>
		<select id="slivkan" class="form-control">
			<option value="">Select One</option>
		</select>
	</div>
	<div class="form-group col-md-2 col-sm-4 col-xs-6">
		<label for="qtr" class="control-label">Quarter:</label>
		<select id="qtr" class="form-control"></select>
	</div>
	<div class="col-md-6 col-md-offset-1 hidden-xs">
		<table class="table table-bordered table-condensed">
			<thead>
				<tr>
					<th>Type</th>
					<th>Events</th>
					<th>IMs</th>
					<th>Helper</th>
					<th>Committee</th>
					<th>Other</th>
					<th>Total</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>Subtotal</th>
					<td class="eventPoints"></td>
					<td class="imPoints"></td>
					<td class="helperPoints"></td>
					<td class="committeePoints"></td>
					<td class="otherPoints"></td>
					<td class="totalPoints"></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-xs-12 visible-xs">
		<table class="table table-bordered table-condensed">
			<thead>
				<th>Type</th>
				<th>Subtotal</th>
			</thead>
			<tbody>
				<tr>
					<td>Events</td>
					<td class="eventPoints"></td>
				</tr>
				<tr>
					<td>IMs</td>
					<td class="imPoints"></td>
				</tr>
				<tr>
					<td>Helper</td>
					<td class="helperPoints"></td>
				</tr>
				<tr>
					<td>Committee</td>
					<td class="committeePoints"></td>
				</tr>
				<tr>
					<td>Other</td>
					<td class="otherPoints"></td>
				</tr>
				<tr>
					<td>Total</td>
					<td class="totalPoints"></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="row breakdown">
	<div class="col-md-4">
		<div id="otherPointsTable"></div>
		<div class="chart" id="eventsChart"></div>
		<div class="chart" id="imsChart"></div>
	</div>
	<div class="col-md-4">
		<table class="table table-bordered table-condensed table-striped">
			<thead>
				<tr>
					<th>Events Attended</th>
				</tr>
			</thead>
			<tbody id="attendedEvents">
			</tbody>
		</table>
	</div>
	<div class="col-md-4">
		<table class="table table-bordered table-condensed table-striped">
			<thead>
				<tr>
					<th>Events Unattended</th>
				</tr>
			</thead>
			<tbody id="unattendedEvents">
			</tbody>
		</table>
	</div>
</div>
<?php include('credits.html'); ?>

<script id="eventsTemplate" type="text/template">
<% if (events.length > 0) {
	forEach(events, function(event) {
		%>
	    <tr><td class="<%= event.committee %>"><%= event.event_name %></td></tr>
		<%
	})
} else {
	print('<tr><td>None</td></tr>')
} %>
</script>

<script id="otherPointsTableTemplate" type="text/template">
<% if (other_breakdown.length > 0 && other_breakdown[0][0]) { %>
<table class="table table-bordered table-condensed table-striped">
	<thead>
		<tr>
			<th>Other Points Breakdown</th>
			<th>Pts</th>
		</tr>
	</thead>
	<tbody>
	<% forEach(other_breakdown, function(other) {
		%>
		<tr>
			<td><%= other[0] %></td>
			<td><%= other[1] %></td>
		</tr>
		<%
	}) %>
	</tbody>
</table>
<% } %>
</script>
