<!-- // TEMPLATE For FunkPHP Rendered Pages (the final step unless you already sent a JSON Response!)
    /****** HOW TO USE THIS FILE: ******/
    // Write your Page using HTML and the typical Template Engine Syntax you are used to!) Make sure
    // the filename is exactly as in 'page'=> 'page_file_name_to_template_return' in the routes file.
    //
    //
    /****** AVAILABLE TEMPLATE ENGINE SYNTAX ******
    * - {{d.variable}} / {{ d.variable }} = Output the variable value (HTML escaped), outputs "" if null
    * - It is replaced with `<PHP_TAG_START echo htmlspecialchars($c['d'][$variable] ?? "", ENT_QUOTES, 'UTF-8'); PHP_TAG_END>`
    *
    * - {{arrayKey.variable}} / {{ arrayKey.variable }} = Output the associative array key variable value (HTML escaped), outputs "" if null
    * - It is replaced with `<PHP_TAG_START echo htmlspecialchars($c['d'][$arrayKey][$variable] ?? "", ENT_QUOTES, 'UTF-8'); PHP_TAG_END>`
    *
    * - {{d.variable|"WhenNull"}} / {{ d.variable|"WhenNull" }} = Output the variable value (HTML escaped) or "WhenNull" if null
    * - It is replaced with `<PHP_TAG_START echo htmlspecialchars($c['d'][$variable] ?? 'WhenNull', ENT_QUOTES, 'UTF-8'); PHP_TAG_END>`
    *
    * - {!!!!?variable?!!!!} = Output the variable value (HTML NOT escaped) - ONLY USE IF YOU 100 % SANITIZED THE VARAIBLE!!!!
    *
    * - {% if(condition) %} = Start of an if statement, remember the {% endif %} at the end of the statement!
    *
    * - {% elseif(condition) %} = Else if statement, can be used multiple times in a row; remember the {% endif %} at the end of the statement!
    *
    * - {% else %} = Else statement, can be used once and should be used before {% endif %} remember the {% endif %} at the end of the statement!
    *
    * - {% endif %} = End of an if statement, remember to use {% if %} and/or {% elseif %} and/or {% else %} before you use this statement!
    *
    * - {% foreach($loop_design) %} = Start of a foreach loop, remember to use {% endforeach %} at the end of the loop!
    * - For example: {% foreach(d.array as d.array.element) %} OR {% foreach(d.array as d.array.key => d.array.element) %}
    *
    * - {% endforeach %} = End of a foreach loop, remember to use @foreach before you use this statement!
    *
    * - {% part(filename) %} = includes the filename with default DIR at "funkphp/pages/parts/"
    * - For example: {% part('header') %} will include the file "funkphp/pages/parts/header.php"
    *
    * - {% auth() %} = Auth check, if the user is logged in, it will show the content inside the {% auth() %} and {% endauth %} tags
    *
    * - {% endauth %} = End of the auth check, remember to use {% auth %} before you use this statement!
    *
    * - {% csrf() %} = CSRF check, either outputs a new CSRF token or the CSRF token from the request
    * - Put this inside of a <form> tag to use it! There is no need for a {% endcsrf %} tag after this!
    *
    -->
<html lang="en">

</html>