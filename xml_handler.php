<?php
function not_found()
{
Header("Content-type: text/xml");
$xmlw = new XMLWriter();
$xmlw -> openMemory();
$xmlw -> setIndent(true);
$xmlw -> setIndentString('');
$xmlw -> startDocument('1.0', 'UTF-8', 'no');
$xmlw -> startElement('document');
$xmlw -> writeAttribute('type', 'freeswitch/xml');
$xmlw -> startElement('section');
$xmlw -> writeAttribute('name', 'result');
$xmlw -> startElement('result');
$xmlw -> writeAttribute('status', 'not found');
$xmlw -> endElement(); //end result
$xmlw -> endElement(); //end section
$xmlw -> endDocument(); //end document
echo $xmlw -> outputMemory();
return TRUE;
}
function directory()
{
global $_SERVER;
global $_POST;
# connect to mysql
$host = 'localhost';
$user = 'freeswitch';
$pass = 'pass';
$db = 'freeswitch';
#$pdo = new PDO ("pgsql:host=$host;port=5432;dbname=$db;user=$user;password=$pass");
$conn = pg_connect ("host=$host dbname=$db user=$user password=$pass");
if (!$conn) {
die("Could not open connection to database server");
}


$query = "SELECT * FROM extensions WHERE userid='" . $_POST['user'] . "' and domain='" . $_POST['domain'] . "'";
$result = pg_query($conn, $query)or die("Error in query: $query." . pg_last_error($conn));
$result_dom = pg_query($conn, $query)or die("Error in query: $query." . pg_last_error($conn));

#                $sql = $pdo->prepare("SELECT * FROM extensions WHERE userid='" . $_POST['user'] . "'");
#                $sql->execute();

# perform the query

#$result = $sql->fetch(PDO::FETCH_ASSOC);
#$result = $sql->fetch();
#$result = $pdo->query($sql);
// Запрос на домен
$dom = pg_fetch_assoc($result_dom);


$num_rows = pg_num_rows($result);

if($num_rows==0){
# if no database row, fallback to filesystem, no FS error
not_found();
return TRUE;
}
Header("Content-type: text/xml");
$xmlw = new XMLWriter();
$xmlw -> openMemory();
$xmlw -> setIndent(true);
$xmlw -> setIndentString('');
$xmlw -> startDocument('1.0', 'UTF-8', 'no');
$xmlw -> startElement('document');
$xmlw -> writeAttribute('type', 'freeswitch/xml');
$xmlw -> startElement('section');
$xmlw -> writeAttribute('name', 'directory');
$xmlw -> startElement('domain');
//$xmlw -> writeAttribute('name', '$${domain}');
$xmlw -> writeAttribute('name', $dom['domain']);
$xmlw -> startElement('params');
$xmlw -> startElement('param');
$xmlw -> writeAttribute('name', 'dial-string');
$xmlw -> writeAttribute('value', '{^^:sip_invite_domain=${dialed_domain}:presence_id=${dialed_user}@${dialed_domain}}${sofia_contact(*/${dialed_user}@${dialed_domain})},${verto_contact(${dialed_user}@${dialed_domain})}');
$xmlw -> endElement(); //end param
$xmlw -> endElement(); //end param
//while($row = $sql->fetch(PDO::FETCH_ASSOC)) {
while( $row = pg_fetch_assoc($result) ) {
$xmlw -> startElement('user');
$xmlw -> writeAttribute('id', $row['userid']);
// Params
$xmlw -> startElement('params');
$xmlw -> startElement('param');
$xmlw -> writeAttribute('name', 'password');
$xmlw -> writeAttribute('value', $row['password']);
$xmlw -> endElement();
$xmlw -> startElement('param');
$xmlw -> writeAttribute('name', 'vm-password');
$xmlw -> writeAttribute('value', $row['vmpasswd']);
$xmlw -> endElement();
$xmlw -> startElement('param');
$xmlw -> writeAttribute('name', 'max-registrations-per-extension');
$xmlw -> writeAttribute('value', $row['maxregistrationsperextension']);
$xmlw -> endElement();
$xmlw -> startElement('param');
$xmlw -> writeAttribute('name', 'sip-force-expires');
$xmlw -> writeAttribute('value', $row['sipforceexpires']);
$xmlw -> endElement();
$xmlw -> startElement('param');
$xmlw -> writeAttribute('name', 'sip-expires-max-deviation');
$xmlw -> writeAttribute('value', $row['sipexpiresmaxdeviation']);
$xmlw -> endElement();
$xmlw -> endElement(); //end params
// Variables
$xmlw -> startElement('variables');
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'toll_allow');
$xmlw -> writeAttribute('value', 'domestic,international,local');
$xmlw -> endElement();
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'accountcode');
$xmlw -> writeAttribute('value', $row['accountcode']);
$xmlw -> endElement();
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'user_context');
$xmlw -> writeAttribute('value', $row['user_context']);
$xmlw -> endElement();
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'effective_caller_id_name');
$xmlw -> writeAttribute('value', $row['displayname']);
$xmlw -> endElement();
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'effective_caller_id_number');
$xmlw -> writeAttribute('value', $row['userid']);
$xmlw -> endElement();
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'outbound_caller_id_name');
$xmlw -> writeAttribute('value', $row['outbound_caller_id_name']);
$xmlw -> endElement();
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'outbound_caller_id_number');
$xmlw -> writeAttribute('value', $row['outbound_caller_id_number']);
$xmlw -> endElement();
$xmlw -> startElement('variable');
$xmlw -> writeAttribute('name', 'callgroup');
$xmlw -> writeAttribute('value', $row['callgroup']);
$xmlw -> endElement();
$xmlw -> endElement(); //end variables
$xmlw -> endElement(); //end user
} // end while
$xmlw -> endElement(); //end domain
$xmlw -> endElement(); //end section
$xmlw -> endDocument(); //end document
echo $xmlw -> outputMemory();
return TRUE;
}
if (isset($_POST['section'])) {
if($_POST['section']=='directory') {
directory();
} else {
# if section is not directory, fallback to filesystem, no FS error
not_found();
}
}
?>