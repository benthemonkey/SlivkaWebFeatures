<!DOCTYPE HTML>
<html lang="en">
<head>
  <?php include('header.html'); ?>
  <title>Submission - Slivka Points Center</title>
  <script type="text/javascript" src="js/pointsSubmission.js"></script>
  <style type="text/css">
    label{
      margin-top: 10px;
    }
    .ui-datepicker-div{
      z-index: 5;
    }
    .no-bottom{
      margin-bottom: 0px;
    }

    #slivkan-entry-tab-buttons{
      margin-bottom: 10px;
      display: block;
    }

    .input-append, input-prepend{
      margin-bottom: 0px;
    }

    .control-group{
      margin-bottom: 10px;
    }

    .tight .row-fluid [class*="span"]{
      margin-top: 0px;
      margin-bottom: 0px;
      min-height: 0px;
    }
    .tight p{
      margin-top: 0px;
      margin-bottom: 0px;
      min-height: 0px;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="offset1 span12 content">
      <?php include('nav.html'); ?>
      <form autocomplete="off" onsubmit="return false;">
        <fieldset>
          <legend>&nbsp;&nbsp;Points Submission Form</legend>
          <div class="row-fluid">
            <div class="span6 col">
              <div class="control-group filled-by-control">
                <label class="control-label" for="filled-by">Points filled out by:</label>
                <input type="text" name="filled-by" id="filled-by" class="input span10" onfocus="$('.filled-by-control').addClass('warning')" onfocusout="validateFilledBy()">
              </div>

              <label for="type">Type:</label>
              <div class="btn-group" id="type" data-toggle="buttons-radio">
                <input type="button" name="p2p" value="P2P"  class="btn type-btn">
                <input type="button" name="im" value="IM" class="btn type-btn">
                <input type="button" name="house meeting" value="House Meeting" class="btn type-btn">
                <input type="button" name="other" value="Other" class="btn type-btn active">
              </div>

              <div class="control-group event-control">
                <label class="control-label" for="event">Event name:</label>
                <input type="text" name="event" id="event" class="input span10" onfocus="$('.event-control').addClass('warning');" onfocusout="validateEventName()">
                <div class="help-block hide" id="event-name-error">Event name + date combination taken</div>
                <div class="help-block hide" id="event-name-length-error">Event name must be between 8 and 40 characters.<br/><span id="event-name-length-error-count"></span></div>
              </div>

              <div class="control-group">
                <label for="date">Date:</label>
                <!--<div id="date-val" class="uneditable-input text-center span2"></div>-->
                <input type="text" id="date-val" name="actual-date" style="display: none">
                <input type="text" id="date" name="date" class="input span4" style="position: relative; z-index: 10;">
              </div>
              <!--<div id="date"></div>-->

              <div class="control-group im-team-control hide">
                <label class="control-label" for="im-team">Team:</label>
                <select id="im-team" class="input span4" onchange="validateIMTeam()">Soccer Softball Ultimate
                  <option>Co-Rec Soccer</option>
                  <option>Co-Rec Softball</option>
                  <option>Co-Rec Ultimate</option>
                  <option>White Soccer</option>
                  <option>White Softball</option>
                  <option>White Ultimate</option>
                </select>
              </div>

              <div class="control-group committee-control">
                <label class="control-label">Committee:</label>
                <select id="committee" class="input span4" onchange="validateCommittee()">
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
                <textarea rows="3" name="description" id="description" class="input span10" onfocusout="validateDescription();"></textarea>
                <div class="help-block hide" id="description-length-error">Be enthusiastic and descriptive!</div>
              </div>

              <div class="control-group">
                <label class="control-label" for="comments">Comments:</label>
                <small class="help-block no-bottom">No-shows, issues with form, explanation of helper points, etc.</small>
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
                  <div class="alert alert-success hide" id="sort-alert">
                    <button type="button" class="close" onclick="$('#sort-alert').slideUp()">&times;</button>
                    <small>Sorted names!</small>
                  </div>
                  <div class="alert alert-warning hide" id="duplicate-alert">
                    <button type="button" class="close" onclick="$('#duplicate-alert').slideUp()">&times;</button>
                    <small>Duplicate name!</small>
                  </div>
                  <div id="slivkan-entry-tab-buttons">
                    <div class="btn" role="button" data-toggle="modal" data-target="#QR-entry"><i class="icon-qrcode"></i> QR</div>
                    <div class="btn" onclick="sortEntries()"><i class="icon-list" title="Sort"></i><i class="icon-arrow-down"></i></div>
                  </div>  
                  <div class="control-group input-prepend input-append slivkan-entry-control" style="margin-left: 1px; width: 95%">
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
                  <div class="control-group fellow-entry-control no-bottom">
                    <input type="text" class="fellow-entry" name="fellow-entry" placeholder="Fellow">
                  </div>
                </div>
              </div>
              <div class="help-block span12"><small>Additional inputs appear automatically.</small></div>
            </div>
          </div>
          <div class="form-actions">
            <div class="help-block alert alert-error span6 pull-right hide" id="submit-error"></div>
            <button type="submit" class="btn btn-primary btn-large" onclick="validatePointsForm()" >Validate</button>
            <button type="button" class="btn btn-large" onclick="$('#real-reset').fadeToggle()">Reset</button>
            <button type="button" class="btn btn-danger hide" id="real-reset" onclick="resetForm()">Really Reset?</button>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
  <div id="QR-entry" class="modal hide fade" role="dialog" style="width: auto;">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3>Paste Names from QR Codes</h3>
    </div>
    <div class="modal-body">
      <textarea name="bulk-names" id="bulk-names" rows="12" class="span5" onkeyup="processBulkNames()"></textarea>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn" data-dismiss="modal">Cancel</a>
      <a href="#" class="btn btn-primary" onclick="addBulkNames()" data-dismiss="modal" >Add Names</a>
    </div>
  </div>
  <div id="submit-results" class="modal hide fade" role="dialog" >
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3>Points Form Submission</h3>
    </div>
    <div class="modal-body">
      <div>
        <h4>Receipt:</h4>
      </div>
      <table class="table table-bordered table-condensed" id="receipt">
        <tbody>
          <tr class="warning">
            <td>Status</td>
            <td id="results-status">Unsubmitted</td>
          </tr>
          <tr class="results-row">
            <td class="results-label"></td>
            <td class="results"></td>
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
</body>
</html>