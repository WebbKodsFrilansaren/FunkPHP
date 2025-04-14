### HOW TO USE MIDDLEWARE In FunkPHP

## Written first: 14th April 2025,

## Last Updated: 14th April 2025

- Step 1: Create a new file in "/src/funkphp/middlewares/" and name it after your middleware handler name. For example: "MW_ROOT.php". There is no forced naming convention, but "MW\_" would help you to know what kind of handler you are calling.

- Step 2: Your created "MW_ROOT.php" file should be in the style of:

```
<?php return function(&$c, $optionalVariables){/* Do something with $c as reference variable and/or the other variables */} ?>
```

- Step 3: In your middleware function, do whatever you need to while you have access to all loaded configurations, the current data retrieved and processed by the request so far and whatever you chose to provide it during the initialization (STEP0 - see /src/funkphp/dx_steps/STEP0_INITIALIZE_GLOBAL_CONFIG.php). Now you can rinse and repeat with the remaining middleware(s)!

## IMPORTANT WHEN USING MIDDLEWARES in FunkPHP

### Minding the order of your Middlewares!

- Mind the order of your Middlewares as they are defined by the level of your routes in: "/src/funkphp/routes/middleware_routes.php".

- For example: the route GET => "/" would mean any middleware there would run for GET => "/users/", GET => "/users/:id" and so on.

### Exiting the execution of Middlewares early!

- Look inside of `$c['req']['keep_running_mws']`. This is set to `null` until middlewares are started being executed. Then it is set to `true` and checked against `null` or `false` in the foreach loop. It will only run each next middleware if that is still set to `true`.

- If at any point in any middleware being executed the value of `$c['req']['keep_running_mws']` is set to `null` or `false`, it will stop executing after the current middleware. That is essentially the same as if the matched route had none middlewares to begin with.
