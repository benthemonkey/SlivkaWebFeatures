<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Submission - Slivka Points Center</title>
  <!--<script type="text/javascript" src="js/pointsSubmission.js"></script>-->
  <style type="text/css">
    #slivkan-entry-tab-buttons{
      margin-bottom: 10px;
      display: block;
    }

    .slivkan-entry-control{
      margin-left: 1px; 
      width: 95%;
      margin-bottom: 10px;
    }

    .input-prepend .input-append{
      margin-bottom: 0;
    }

    .btn-group{
      padding-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="content">
      <?php include('nav.html'); ?>
      <form autocomplete="off" onsubmit="return false;">
        <fieldset>
          <legend>&nbsp;&nbsp;Points Submission Form</legend>
          <div class="row-fluid">
            <div class="span6 col">
              <div class="control-group filled-by-control">
                <label class="control-label" for="filled-by">Points filled out by:</label>
                <input type="text" name="filled-by" id="filled-by" class="input span10">
              </div>

              <div class="control-group">
                <label class="control-label" for="type">Type:</label>
                <div class="btn-group" id="type" data-toggle="buttons-radio">
                  <input type="button" name="p2p" value="P2P"  class="btn type-btn">
                  <input type="button" name="im" value="IM" class="btn type-btn">
                  <input type="button" name="house meeting" value="House Meeting" class="btn type-btn">
                  <input type="button" name="other" value="Other" class="btn type-btn active">
                </div>
              </div>

              <div class="control-group im-team-control hide">
                <label class="control-label" for="im-team">Team:</label>
                <select id="im-team" class="input span4"></select>
              </div>

              <div class="control-group event-control">
                <label class="control-label" for="event">Event name:</label>
                <input type="text" name="event" id="event" class="input span10">
                <div class="help-block hide" id="event-name-error">Event name + date combination taken</div>
                <div class="help-block hide" id="event-name-length-error">Event name must be between 8 and 40 characters.<br/><span id="event-name-length-error-count"></span></div>
              </div>

              <div class="control-group">
                <label id="date-label" for="date">Date:</label>
                <input type="text" id="date-val" name="actual-date" style="display: none">
                <!--<input type="text" id="date" name="date" class="input span4" style="position: relative; z-index: 10;">-->
                <div class="input-append">
                  <input type="text" id="date" name="date" class="input text-center" style="position: relative; z-index: 10; color: #000000; width: 50px;" disabled></input>
                </div>
              </div>
              <!--<div id="date"></div>-->

              <div class="control-group committee-control">
                <label class="control-label">Committee:</label>
                <select id="committee" class="input span4">
                  <option>Select One</option>
                  <option>Exec</option>
                  <option>Academic</option>
                  <option>Facilities</option>
                  <option>Faculty</option>
                  <option>Historian</option>
                  <option>IT</option>
                  <option>Philanthropy</option>
                  <option>Social</option>
                  <option>CA</option>
                  <option>Other</option>
                </select>
              </div>

              <div class="control-group description-control">
                <label class="control-label" for="description">Description:</label>
                <textarea rows="3" name="description" id="description" class="input span10"></textarea>
                <div class="help-block hide" id="description-length-error">Be enthusiastic and descriptive!</div>
              </div>

              <div class="control-group">
                <label class="control-label" for="comments">Comments:</label>
                <small class="help-block">No-shows, issues with form, explanation of helper points, etc.</small>
                <textarea name="comments" id="comments" class="input span10" rows="3"></textarea>
              </div>
            </div>
            <div class="span6 col">
              <ul class="nav nav-tabs" id="tabs">
                <li class="active"><a href="#slivkan-entry-tab" data-toggle="tab"><span>Attendees</span></a></li>
                <li><a href="#fellow-entry-tab" data-toggle="tab"><span>Fellows</span></a></li>
              </ul>

              <div class="tab-content span11">
                <div class="tab-pane active" id="slivkan-entry-tab" >
                  <!--<div class="help-block span12"><small>Names saved every 10 seconds until submit/reset.</small></div>-->
                  <div class="alert alert-success hide" id="sort-alert">
                    <button type="button" class="close" id="close-sort-alert">&times;</button>
                    <small>Sorted names!</small>
                  </div>
                  <div class="alert alert-warning hide" id="duplicate-alert">
                    <button type="button" class="close" id="close-dupe-alert">&times;</button>
                    <small>Duplicate name!</small>
                  </div>
                  <div id="slivkan-entry-tab-buttons">
                    <div class="btn" role="button" data-toggle="modal" data-target="#QR-entry" title="add QR Codes / Wildcards"><i class="icon-qrcode"></i> Bulk Entry <i class="icon-barcode"></i></div>
                    <div class="btn" id="sort-entries"><i class="icon-list" title="Sort"></i><i class="icon-arrow-down"></i></div>
                  </div>  
                  <div class="control-group input-prepend input-append slivkan-entry-control">
                    <div class="add-on">1</div>
                    <input type="text" class="slivkan-entry span10" name="slivkan-entry" placeholder="Slivkan">
                    <div class="input-append">
                      <div class="btn committee-point" title="Committee Member" style="display: none"><i class="icon-user"></i></div>
                    </div>
                    <div class="input-append">
                      <div class="btn helper-point" title="Helper Point" style="display: none"><i class="icon-thumbs-up"></i></div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane" id="fellow-entry-tab">
                  <div class="control-group input-prepend fellow-entry-control">
                    <div class="add-on">1</div>
                    <input type="text" class="fellow-entry" name="fellow-entry" placeholder="Fellow">
                  </div>
                </div>
              </div>
              <div class="help-block span12"><small>Additional inputs appear automatically.</small></div>
            </div>
          </div>
          <div class="form-actions">
            <div class="help-block alert alert-error span6 pull-left hide" id="submit-error"></div>
            <div class="pull-right">
              <button type="submit" class="btn btn-primary btn-large" id="submit">Validate</button>
              <button type="button" class="btn btn-large" id="reset">Reset</button>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
  <div id="QR-entry" class="modal hide fade" role="dialog" style="width: auto;">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3>Paste Names / Wildcard Barcodes </h3>
    </div>
    <div class="modal-body">
      <textarea name="bulk-names" id="bulk-names" rows="12" class="span5"></textarea>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn" data-dismiss="modal">Cancel</a>
      <a href="#" class="btn btn-primary" id="add-bulk-names" data-dismiss="modal" >Add Names</a>
    </div>
  </div>
  <div id="submit-results" class="modal hide fade" role="dialog" >
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3>Points Form Submission</h3>
    </div>
    <div class="modal-body">
      <table class="table table-bordered table-condensed" id="receipt">
        <tbody>
          <tr class="warning">
            <td>Status</td>
            <td id="results-status">Unsubmitted</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <div id="unconfirmed">
        <button type="button" class="btn btn-primary" id="real-submit">Submit</button>
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
      </div>
      <div id="confirmed" class="hide">
        <span>If an error occurs, email the receipt to Ben Rothman.</span>
        <a href="table.php" class="btn btn-primary">View Points</a>
      </div>
    </div>
  </div>
  <script type="text/javascript"> 
    $(document).ready(function(){ pointsCenter.submission.init() });
  </script>
</body>
</html>