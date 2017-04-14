# SMS - SMS Sending module for bravedave/DVC

* Works with smsbroadcast.com.au
* could be developed with https://www.nexmo.com/

## Example
```
	$account = new sms\account;
		$account->enabled = TRUE;
		$account->countrycode = 'AU';
		$account->providor = 'smsbroadcast';
		$account->accountid = '<your account name>';
		$account->accountpassword = '<your password>';
		$account->fromnumber = '<your mobile number>';

	$sms = new sms\sms( $account);

	print $sms->balance();
	print $sms->send( '<to number>', 'hello world');
```
