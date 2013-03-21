


var validationMessage(name, eventname, identifier){
	return '<h2>Slivka Points Correction</h2>
<h3>Automated Email</h3>

<p style="padding: 10; width: 70%">'+name+' has submitted a points correction for the 
event, '+eventname+', for which you took points. Please click one of the following links 
to respond to this request. Please do so within 2 days of receiving this email.</p>

<ul>
    <li><a href="link1">'+name+' was at '+eventname+'</a></li>
	<li><a href="link2">'+name+' was NOT at '+eventname+'</a></li>
	<li><a href="link3">Not sure</a></li>
</ul>

<p style="padding: 10; width: 70%">If you received this email in error, please contact BenSRothman@gmail.com</p>';
}