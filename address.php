<?php

/**
 * @author Chris S - AKA Someguy123
 * @version 0.01 (ALPHA!)
 * @license PUBLIC DOMAIN http://unlicense.org
 * @package +Coin - Bitcoin & forks Web Interface
 */

//ini_set("display_errors", false);

include ("header.php");
?>

<!-- Java Script -->
<script type='text/javascript'>

$(document).on("click", ".open-EditAddrDialog", function () {
     var myAddrId = $(this).data('id');
     $("#myAddress").html(myAddrId);
     $(".modal-body #myAddress").val( myAddrId );

     var myAddrName = $(this).data('name');
     $(".modal-body #AddrName").val( myAddrName );

    $('#EditAddrDialog').modal('show');
});

$(document).on("click", ".open-SignAddrDialog", function () {
     var myAddrId = $(this).data('id');
     $("#signAddress").html(myAddrId);
     $(".modal-body #signAddress").val( myAddrId );

     var myAddrName = $(this).data('name');
     $(".modal-body #SignAddrName").val( myAddrName );

    $('#SignAddrDialog').modal('show');
});

</script>

<?php
if (isset($_POST['addaddr']) && isset($_POST['account']))
{
  $nmc->getnewaddress($_POST['account']);
}

if (isset($_POST['addacc']) && isset($_POST['account']))
{
  $nmc->getaccountaddress($_POST['account']);
}


$myaddresses = file("myaddresses.csv");
$myaddress_arr = array();
foreach ($myaddresses as $line)
{
    $values = explode(";", $line);
    $address = $values[0];
    $name = str_replace("\n", "", $values[1]);
    $myaddress_arr[$address] = $name;
}

// change address
if (isset($_POST['AddrName']) && isset($_POST['myAddress']))
{
        $myaddress_arr[$_POST['myAddress']] = $_POST['AddrName'];

        $f = fopen("myaddresses.csv", "w");

        foreach ($myaddress_arr as $address => $name)
        {
            $line = $address.";".$name."\n";
            fputs($f, $line);
        }
        fclose($f);
}

// sign message
if (isset($_POST['message']) && isset($_POST['signAddress']) && isset($_POST['walletPass']))
{
	$nmcreply = $nmc->walletpassphrase($_POST['walletPass'], 30);
        $signature = $nmc->signmessage($_POST['signAddress'], $_POST['message']);

	if ($signature == "")
	{
		echo "<div class='content'>
		<b>Incorrect wallet password, or any HTTP error 500 was returned by daemon.</b>
		</div>";
	} else {
		echo "<div class='content'>
		<b>The requested signature is:</b> <font color=green>" . $signature . "</font>
		</div>";
	}
}


$addr = $nmc->listaccounts();
// $addrkeys = array_keys($addr);
echo "<div class='content'>
<h2>Select an account to get a list of an addresses</h2>";
echo "<form action='address.php' method='POST'>
<input name='account'>
<input class='btn' name='addacc' type='submit' value='Add Account' />
</form>";

echo "<form action='address.php' method='POST'>
<select name='account'>";
foreach ($addr as $account => $balance)
{
        $selected = "";
        if (isset($_POST['account']))
        {
           settype($account, "string");
       if ($_POST['account'] == $account)
          $selected = "selected";
        }
    echo "<option value='{$account}' $selected>{$account} ({$balance})</option>";
}
echo "</select>
<input class='btn' type='submit' value='View addresses' />
<input class='btn' name='addaddr' type='submit' value='Add address' />
</form>";

if (isset($_POST['account']))
    $account = $_POST['account'];
else
    $account = "";

        echo "<table class='table-striped table-bordered table-condensed table'>
<thead><tr><th colspan='2'>Addresses for Account '".$account."'</th></tr></thead>";
        foreach ($nmc->getaddressesbyaccount($account) as $address)
        {
                $address_label = $myaddress_arr[$address];
                echo "<tr><td>" . $address . "</td>
                      <td>" . $address_label . "</td>
                          <td><a data-id='".$address."' data-name='".$address_label."' data-toggle='modal' href='#EditAddrDialog' class='open-EditAddrDialog btn btn-mini'>Edit label</a>
                              <a data-id='".$address."' data-button_name='".$address_label."' data-toggle='modal' href='#SignAddrDialog' class='open-SignAddrDialog btn btn-mini'>Sign msg</a></td></tr>";
        }
        echo "</table>";
?>


<!-- Edit button -->
<form action='address.php' method='POST'>
<!-- Modal --->
<div id="EditAddrDialog" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 id="myModalLabel">Change Address Name</h3>
        </div>
        <div class="modal-body">
        <table><tr>
          <td><div>Address to change</div></td>
          <td>&nbsp; &nbsp;<input type="text" name="AddrName" id="AddrName" value="Name"/></td>
        </tr></table>
        <input type="hidden" name="myAddress" id="myAddress" value="Nothing"/>
        <input type="hidden" name="account" id="account" value="<?php echo $account ?>"/>
        </div>
        <div class="modal-footer">
                <button class="btn" data-dismiss="modal">Close</button>
                <button class="btn btn-primary">Save Changes</button>
        </div>
</div>
</form>

<!-- Sign button -->
<form action='address.php' method='POST'>
<!-- Modal --->
<div id="SignAddrDialog" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 id="myModalLabel_sign">Sign a message</h3>
        </div>
        <div class="modal-body">
        <table
          <tr><td>Wallet password: &nbsp; &nbsp;<input type="password" name="walletPass" id="walletPass" value=""/></td></tr>
          <tr><td><div id="myLabel">Message to sign:</div></td></tr>
          <tr><td><textarea name="message" id="message" value="" cols="120" rows=5 style="width: 100%;"></textarea></td></tr>
        </table>
        <input type="hidden" name="signAddress" id="signAddress" value="Nothing"/>
        </div>
        <div class="modal-footer">
                <button class="btn" data-dismiss="modal">Close</button>
                <button class="btn btn-primary">Sign Message</button>
        </div>
</div>
</form>
<?php
echo"</div>";

echo "</div>";
include ("footer.php");
?>
