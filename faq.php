<!DOCTYPE HTML>
<html lang="en" manifest="pointscenter.appcache">
<head>
  <?php include('header.html'); ?>
  <title>FAQ - Slivka Points Center</title>
  <script type="text/javascript">
  $(document).ready(function(){
    $('.nav li').eq(4).addClass('active');
  })
  </script>
</head>
<body>
	<div class="container-fluid">
		<div class="content">
			<?php include('nav.html'); ?>
      <div class="row-fluid">
        <legend>&nbsp;&nbsp;FAQ</legend>
        <div class="accordion col" id="accordion">
          <div class="accordion-group">
            <div class="accordion-heading">
              <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
                What are points?
              </a>
            </div>
            <div id="collapseOne" class="accordion-body collapse in">
              <div class="accordion-inner">
                Housing points are points that determine the eligibility of a resident to return to Slivka. By the end of Winter Quarter, these points are totaled and ranked by amount. Then, starting from the top of the list, Slivkans are invited to return until all available slots have been filled. The Vice President is responsible for recording housing points and consistently reporting them to Slivkans. Discrepancies can be addressed with the Vice President to rectify any errors held valid by the Vice President.
              </div>
            </div>
          </div>
          <div class="accordion-group">
            <div class="accordion-heading">
              <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
                How do I get points?
              </a>
            </div>
            <div id="collapseTwo" class="accordion-body collapse">
              <div class="accordion-inner">
                Points are awarded by participation throughout the academic year until Winter Quarter. At Slivka, we want everyone to get involved and become part of the Slivkan community. Thus, points are rewards for attending (or even hosting) various Slivkan events such as munchies, firesides, Peer-to-Professor (P2P) lunches, Student-Faculty Receptions, group bonding, house meetings, intramural sports, committees, maintenance, and much more!
              </div>
            </div>
          </div>
          <div class="accordion-group">
            <div class="accordion-heading">
              <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">
                How many points do I get for each event?
              </a>
            </div>
            <div id="collapseThree" class="accordion-body collapse">
              <div class="accordion-inner">
                <p>Unless otherwise specified, events award one point per attendance.</p>
                <ul>
                  <li>Peer-to-Professor (P2P) lunches are twice a week but earn one point per week.</li>
                  <li>Intramural sports (IM sports) award one point per game when at least three games of that sport are attended.</li>
                  <li>Committee members may earn up to 20 points per quarter.</li>
                  <li>Executive board members earn 40 points per quarter.</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>