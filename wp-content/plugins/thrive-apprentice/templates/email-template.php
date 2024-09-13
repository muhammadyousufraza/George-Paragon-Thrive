<?php

echo __( "You have a new signup from Thrive Apprentice register page" ) . "\n\n";

echo __( "Contact details submitted", "thrive-apprentice" ) . ": " . "\n\n";

echo __( "Name", "thrive-apprentice" ) . ": " . $request['first_name'] . "\n";
echo __( "Email", "thrive-apprentice" ) . ": " . $request['user_email'] . "\n";

