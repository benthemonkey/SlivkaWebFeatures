<!DOCTYPE HTML>
<html lang="en">
<head>
	<?php include('header.html'); ?>
	<title>Course Database</title>
</head>
<body style="background: none;">
<p>Want to know who else in Slivka has taken your classes? Search for your department and course here to find out!</p>
<form method="get" action="">
	<table class="table">
		<tr>
			<td>Department:</td>
			<td>
				<select name="department" id="department" tabindex="29" size="1" style="width:252px;">
					<option value="" selected="">Select a department</option>
					<option value="AAL">AAL - African and Asian Languages</option>
					<option value="ACCOUNT">ACCOUNT - Accounting</option>
					<option value="ACCT">ACCT - Accounting &amp; Info Systems</option>
					<option value="ACCTX">ACCTX - Accounting &amp; Info Systems</option>
					<option value="ADVT">ADVT - Advertising</option>
					<option value="AFST">AFST - African Studies</option>
					<option value="AF_AM_ST">AF_AM_ST - African American Studies</option>
					<option value="ALT_CERT">ALT_CERT - Alternative Certification</option>
					<option value="AMER_ST">AMER_ST - American Studies</option>
					<option value="AMES">AMES - Asian &amp; Middle Eastern Studies</option>
					<option value="ANTHRO">ANTHRO - Anthropology</option>
					<option value="APP_PHYS">APP_PHYS - Applied Physics</option>
					<option value="ARABIC">ARABIC - Arabic</option>
					<option value="ART">ART - Art Theory &amp; Practice</option>
					<option value="ART_HIST">ART_HIST - Art History</option>
					<option value="ASIAN_AM">ASIAN_AM - Asian American Studies</option>
					<option value="ASTRON">ASTRON - Astronomy</option>
					<option value="BIOL_SCI">BIOL_SCI - Biological Sciences</option>
					<option value="BLAW">BLAW - Business Law</option>
					<option value="BLAWX">BLAWX - Business Law</option>
					<option value="BMD_ENG">BMD_ENG - Biomedical Engineering</option>
					<option value="BUSCOM">BUSCOM - Business and Commercial Law</option>
					<option value="BUS_ALYS">BUS_ALYS - Business Analyst</option>
					<option value="BUS_INST">BUS_INST - Business Institutions</option>
					<option value="CFS">CFS - Chicago Field Studies</option>
					<option value="CHEM">CHEM - Chemistry</option>
					<option value="CHEM_ENG">CHEM_ENG - Chemical Engineering</option>
					<option value="CHINESE">CHINESE - Chinese</option>
					<option value="CHSS">CHSS - Comp &amp; Hist Social Science</option>
					<option value="CIC">CIC - CIC Traveling Scholar</option>
					<option value="CIS">CIS - Computer Information Systems</option>
					<option value="CIV_ENV">CIV_ENV - Civil &amp; Envrnmtl Engineering</option>
					<option value="CLASSICS">CLASSICS - Classics - Readings in Englis</option>
					<option value="CLIN_PSY">CLIN_PSY - Clinical Psychology</option>
					<option value="CLIN_RES">CLIN_RES - Clinical Research &amp; Reg Admin</option>
					<option value="CME">CME - Chicago Metropolitan Exchange</option>
					<option value="CMN">CMN - Communication Related Courses</option>
					<option value="COG_SCI">COG_SCI - Cognitive Science</option>
					<option value="COMM_ST">COMM_ST - Communication Studies</option>
					<option value="COMP_LIT">COMP_LIT - Comparative Literary Studies</option>
					<option value="CONDUCT">CONDUCT - Conducting</option>
					<option value="CONPUB">CONPUB - Constitutional and Public Law</option>
					<option value="COUN_PSY">COUN_PSY - Counseling Psychology</option>
					<option value="CRDV">CRDV - Career Development</option>
					<option value="CRIM">CRIM - Criminal Law</option>
					<option value="CSD">CSD - Comm Sci &amp; Disorders</option>
					<option value="DANCE">DANCE - Dance</option>
					<option value="DECS">DECS - Mngrl Econ &amp; Decision Sci</option>
					<option value="DECSX">DECSX - Mngrl Econ &amp; Decision Sci</option>
					<option value="DIV_MED">DIV_MED - Divorce Mediation Training</option>
					<option value="DSGN">DSGN - Segal Design Institute</option>
					<option value="EARTH">EARTH - Earth and Planetary Sciences</option>
					<option value="ECON">ECON - Economics</option>
					<option value="EECS">EECS - Elect Engineering &amp; Comp Sci</option>
					<option value="ENGLISH">ENGLISH - English</option>
					<option value="ENTR">ENTR - Entrepreneurship</option>
					<option value="ENVR_POL">ENVR_POL - Environmental Policy &amp; Culture</option>
					<option value="ENVR_SCI">ENVR_SCI - Environmental Science</option>
					<option value="EPI_BIO">EPI_BIO - Epidemiology &amp; Biostats</option>
					<option value="ES_APPM">ES_APPM - Engineering Sci &amp; Applied Mat</option>
					<option value="EXMMX">EXMMX - Executive Master in Management</option>
					<option value="FINANCE">FINANCE - Finance</option>
					<option value="FINC">FINC - Finance</option>
					<option value="FINCX">FINCX - Finance</option>
					<option value="FN_EXTND">FN_EXTND - CFP Extended</option>
					<option value="FRENCH">FRENCH - French</option>
					<option value="GBL_HLTH">GBL_HLTH - Global Health</option>
					<option value="GENET_CN">GENET_CN - Genetic Counseling</option>
					<option value="GEN_CMN">GEN_CMN - General Comm &amp; Intro Courses</option>
					<option value="GEN_ENG">GEN_ENG - General Engineering</option>
					<option value="GEN_LA">GEN_LA - General Liberal Arts</option>
					<option value="GEN_MUS">GEN_MUS - General Music</option>
					<option value="GEOG">GEOG - Geography</option>
					<option value="GERMAN">GERMAN - German</option>
					<option value="GNDR_ST">GNDR_ST - Gender Studies</option>
					<option value="GREEK">GREEK - Greek</option>
					<option value="HC_COM">HC_COM - Healthcare Compliance</option>
					<option value="HDPS">HDPS - Human Develop &amp; Psych Svcs</option>
					<option value="HDSP">HDSP - Human Development &amp; Social Pol</option>
					<option value="HEBREW">HEBREW - Hebrew</option>
					<option value="HEMA">HEMA - Health Enterprise Management</option>
					<option value="HINDI">HINDI - Hindi</option>
					<option value="HISTORY">HISTORY - History</option>
					<option value="HQS">HQS - Hlthcare Quality &amp; Pat Safety</option>
					<option value="HSIP">HSIP - Health Sciences Integrated Prg</option>
					<option value="HSR">HSR - Health Services Research</option>
					<option value="HUM">HUM - Humanities</option>
					<option value="IBIS">IBIS - Interdepartmental Bio Sciences</option>
					<option value="IEMS">IEMS - Indust Eng &amp; Mgmt Sciences</option>
					<option value="IGP">IGP - Integ Life Sciences</option>
					<option value="IMC">IMC - Integ Marketing Communication</option>
					<option value="INF_TECH">INF_TECH - Information Technology</option>
					<option value="INTG_SCI">INTG_SCI - Integrated Science</option>
					<option value="INTL">INTL - International Business</option>
					<option value="INTLX">INTLX - International Business</option>
					<option value="INTL_ST">INTL_ST - International Studies</option>
					<option value="IPLS">IPLS - Liberal Studies</option>
					<option value="ISEN">ISEN - Initiative Sustain &amp; Energy</option>
					<option value="ITALIAN">ITALIAN - Italian</option>
					<option value="JAPANESE">JAPANESE - Japanese</option>
					<option value="JAZZ_ST">JAZZ_ST - Jazz Studies</option>
					<option value="JOUR">JOUR - Journalism</option>
					<option value="JRN_WRIT">JRN_WRIT - Journalistic Writing</option>
					<option value="JWSH_ST">JWSH_ST - Jewish Studies</option>
					<option value="JW_LEAD">JW_LEAD - Jewish Leadership</option>
					<option value="KELLG_FE">KELLG_FE - Financial Economics</option>
					<option value="KELLG_MA">KELLG_MA - Managerial Analytics</option>
					<option value="KOREAN">KOREAN - Korean</option>
					<option value="LATIN">LATIN - Latin</option>
					<option value="LATINO">LATINO - Latina and Latino Studies</option>
					<option value="LAWSTUDY">LAWSTUDY - Law Studies</option>
					<option value="LDRSHP">LDRSHP - Leadership</option>
					<option value="LEADERS">LEADERS - Leadership</option>
					<option value="LEAD_ART">LEAD_ART - Art of Leadership</option>
					<option value="LEGAL_ST">LEGAL_ST - Legal Studies</option>
					<option value="LING">LING - Linguistics</option>
					<option value="LIT">LIT - Literature</option>
					<option value="LITARB">LITARB - Litigation and Arbitration</option>
					<option value="LOC">LOC - Learning &amp; Org Change</option>
					<option value="LRN_SCI">LRN_SCI - Learning Sciences</option>
					<option value="MATH">MATH - Mathematics</option>
					<option value="MAT_SCI">MAT_SCI - Materials Science &amp; Eng</option>
					<option value="MBIOTECH">MBIOTECH - Masters in Biotechnology</option>
					<option value="MCW">MCW - Master of Creative Writing</option>
					<option value="MECH_ENG">MECH_ENG - Mechanical Engineering</option>
					<option value="MECN">MECN - Decision Sciences</option>
					<option value="MECNX">MECNX - Decision Sciences</option>
					<option value="MECS">MECS - Managerial Econ &amp; Strategy</option>
					<option value="MEDM">MEDM - Media Management</option>
					<option value="MED_INF">MED_INF - Medical Informatics</option>
					<option value="MED_SKIL">MED_SKIL - Mediation Skills Training</option>
					<option value="MGMT">MGMT - Management</option>
					<option value="MGMTX">MGMTX - Management</option>
					<option value="MHB">MHB - Medical Humanities &amp; Bioethics</option>
					<option value="MIT">MIT - Media Industries &amp; Tech</option>
					<option value="MKTG">MKTG - Marketing</option>
					<option value="MKTGX">MKTGX - Marketing</option>
					<option value="MMSS">MMSS - Math Methods in the Social Sc</option>
					<option value="MORS">MORS - Management and Organizations</option>
					<option value="MORSX">MORSX - Management and Organizations</option>
					<option value="MPD">MPD - Master of Product Development</option>
					<option value="MPPA">MPPA - Master of Public Policy Admin</option>
					<option value="MSA">MSA - Sports Administration</option>
					<option value="MSC">MSC - MS in Communication</option>
					<option value="MSCI">MSCI - Master of Science in Clin Inv</option>
					<option value="MSIA">MSIA - Master of Science in Analytics</option>
					<option value="MSRC">MSRC - Master of Regulatory Complianc</option>
					<option value="MSTP">MSTP - Medical Scientist Training</option>
					<option value="MS_ED">MS_ED - MS in Educ &amp; Social Policy</option>
					<option value="MS_FT">MS_FT - MS in Marital &amp; Family Therapy</option>
					<option value="MS_HE">MS_HE - MS in Higher Ed Admin &amp; Policy</option>
					<option value="MS_LOC">MS_LOC - Learning &amp; Org Change</option>
					<option value="MTS">MTS - Media, Technology &amp; Society</option>
					<option value="MUSEUM">MUSEUM - Museum Studies</option>
					<option value="MUSIC">MUSIC - Music</option>
					<option value="MUSICOL">MUSICOL - Musicology</option>
					<option value="MUSIC_ED">MUSIC_ED - Music Education</option>
					<option value="MUS_COMP">MUS_COMP - Music Composition</option>
					<option value="MUS_HIST">MUS_HIST - Music History &amp; Lit</option>
					<option value="MUS_TECH">MUS_TECH - Music Technology</option>
					<option value="MUS_THRY">MUS_THRY - Music Theory</option>
					<option value="NAV_SCI">NAV_SCI - Naval Science</option>
					<option value="NEUROBIO">NEUROBIO - Neurobiology &amp; Physiology</option>
					<option value="NUIN">NUIN - Neuroscience</option>
					<option value="OPNS">OPNS - Operations Management</option>
					<option value="OPNSX">OPNSX - Operations Management</option>
					<option value="ORG_BEH">ORG_BEH - Organizational Behavior</option>
					<option value="ORTH">ORTH - Orthotics</option>
					<option value="PA">PA - Physician Assistant</option>
					<option value="PBC">PBC - Plant Biology &amp; Conservation</option>
					<option value="PERF_ST">PERF_ST - Performance Studies</option>
					<option value="PERSIAN">PERSIAN - Persian</option>
					<option value="PHIL">PHIL - Philosophy</option>
					<option value="PHIL_NP">PHIL_NP - Philanthropy &amp; Nonprofit Fund</option>
					<option value="PHYSICS">PHYSICS - Physics</option>
					<option value="PHYS_TH">PHYS_TH - Physical Therapy</option>
					<option value="PIANO">PIANO - Piano</option>
					<option value="POLI_SCI">POLI_SCI - Political Science</option>
					<option value="PORT">PORT - Portuguese</option>
					<option value="PPTYTORT">PPTYTORT - Property and Tort Law</option>
					<option value="PREDICT">PREDICT - Predictive Analytics</option>
					<option value="PROJ_MGT">PROJ_MGT - Project Management</option>
					<option value="PROJ_PMI">PROJ_PMI - Project Management</option>
					<option value="PROS">PROS - Prosthetics</option>
					<option value="PSED">PSED - Predictive Science &amp; Engineer</option>
					<option value="PSYCH">PSYCH - Psychology</option>
					<option value="PUB_HLTH">PUB_HLTH - Master's in Public Health</option>
					<option value="QARS">QARS - Qual Assur &amp; Reg Science</option>
					<option value="REAL">REAL - Real Estate</option>
					<option value="RELIGION">RELIGION - Religious Studies</option>
					<option value="RTVF">RTVF - Radio/Television/Film</option>
					<option value="SCS">SCS - School of Continuing Studies</option>
					<option value="SEEK">SEEK - Social Enterprise</option>
					<option value="SEEKX">SEEKX - Social Enterprise</option>
					<option value="SESP">SESP - SESP Core</option>
					<option value="SHC">SHC - Science in Human Culture</option>
					<option value="SLAVIC">SLAVIC - Slavic Languages &amp; Literature</option>
					<option value="SOCIOL">SOCIOL - Sociology</option>
					<option value="SOC_POL">SOC_POL - Social Policy</option>
					<option value="SPANISH">SPANISH - Spanish</option>
					<option value="SPANPORT">SPANPORT - Spanish and Portuguese</option>
					<option value="STAT">STAT - Statistics</option>
					<option value="STRINGS">STRINGS - String Instruments</option>
					<option value="SWAHILI">SWAHILI - Swahili</option>
					<option value="TAXLAW">TAXLAW - Taxation Law</option>
					<option value="TEACH_ED">TEACH_ED - Teacher Education</option>
					<option value="TGS">TGS - TGS General Registrations</option>
					<option value="TH&DRAMA">TH&amp;DRAMA - Theatre &amp; Drama</option>
					<option value="THEATRE">THEATRE - Theatre</option>
					<option value="TURKISH">TURKISH - Turkish</option>
					<option value="URBAN_ST">URBAN_ST - Urban Studies</option>
					<option value="VOICE">VOICE - Voice &amp; Opera</option>
					<option value="WIND_PER">WIND_PER - Wind &amp; Percussion</option>
					<option value="WRITING">WRITING - Writing Arts</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Course:</td>
			<td>
				<select name="course" id="course" tabindex="29" size="1" style="width:252px;"></select>
			</td>
		</tr>
	</table>
</form>
<div id="current"></div>
<div id="past"></div>
<script type="text/javascript" src="bower_components/jquery/jquery.min.js"></script>
<script type="text/javascript">
	//from http://stackoverflow.com/questions/5223/length-of-javascript-object-ie-associative-array
	Object.size = function(obj) {
	    var size = 0, key;
	    for (key in obj) {
	        if (obj.hasOwnProperty(key)) size++;
	    }
	    return size;
	};

	var updateCourses = function(dataString){
	    $.getJSON("ajax/getCoursesInDept.php", dataString, function(data){
	        var out = "<option value=\"\">All</option>";
	        for(var i=0; i<data.length; i++){
	            out += "<option value=\""+data[i]+"\">"+data[i]+"</option>";
	        }

	        $("#course").html(out);
	    });
	},

	updateListing = function(dataString){
	    $.getJSON("ajax/getCourseListing.php", dataString, function(data){
	        var current = "Currently enrolled: ", past = "Enrolled in the past: ";
	        current += data.current.join(", ");
	        past += data.past.join(", ");

	        $("#current").html(current);
	        $("#past").html(past);
	    });
	};

	$(document).ready(function(){
	    $("#department").change(function(){
	        var department = $("#department").val(),
	        course = $("#course").val(),
	        dataString = "department="+department+"&course="+(course === null ? "" : course);

	        if(department.length > 0){
	            updateCourses(dataString);
	            updateListing(dataString);
	        }
	    });

	    $("#course").change(function(){
	        var department = $("#department").val(),
	        course = $("#course").val(),
	        dataString = "department="+department+"&course="+course;

	        if(department.length > 0) updateListing(dataString);
	    });
	});
</script>
</body>
</html>