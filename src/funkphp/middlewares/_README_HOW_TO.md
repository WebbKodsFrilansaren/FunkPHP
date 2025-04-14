### HOW TO USE MIDDLEWARE In FunkPHP

- Step 1: Create a new file in "/src/funkphp/middlewares/" and name it after your middleware handler name. For example: "MW_ROOT.php". There is no forced naming convention, but "MW\_" would help you to know what kind of handler you are calling.

- Step 2: Your created "MW_ROOT.php" file should be in the style of:

```
<?php return function(&$c, $optionalVariables){/* Do something with $c as reference variable and/or the other variables */} ?>
```

- Step 3: In your middleware function, do whatever you need to while you have access to all loaded configurations, the current data retrieved and processed by the request so far and whatever you chose to provide it during the initialization (STEP0 - see /src/funkphp/dx_steps/STEP0_INITIALIZE_GLOBAL_CONFIG.php).
