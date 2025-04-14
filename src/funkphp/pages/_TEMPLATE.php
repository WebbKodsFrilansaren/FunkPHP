<?php // TEMPLATE For FunkPHP Rendered Pages (the final step unless you already sent a JSON Response!)

/****** HOW TO USE THIS FILE: ******/
// Write your Page using HTML and the typical Template Engine Syntax you are used to!) Make sure
// the filename is exactly as in 'page' => 'page_file_name_to_template_return' in the routes file.
//
//
/****** AVAILABLE TEMPLATE ENGINE SYNTAX ******
 * - {{$variable}} / {{ $variable }} = Output the variable value (HTML escaped), outputs "" if null
 *
 * - {{$variable|"WhenNull"}} / {{ $variable|"WhenNull" }} = Output the variable value (HTML escaped) or "WhenNull" if null
 *
 * - {!!!!?$variable?!!!!} = Output the variable value (HTML NOT escaped) - ONLY USE IF YOU 100 % SANITIZED THE VARAIBLE!!!!
 *
 * - @if(condition) = Start of an if statement, remember the @endif at the end of the statement!
 *
 * - @elseif(condition) = Else if statement, can be used multiple times in a row; remember the @endif at the end of the statement!
 *
 * - @else = Else statement, can be used once and should be used before @endif; remember the @endif at the end of the statement!
 *
 * - @endif = End of an if statement, remember to use @if and/or @elseif and/or @else before you use this statement!
 *
 * - @foreach($loop_design) = Start of a foreach loop, remember to use @endforeach at the end of the loop!
 *   - For example: @foreach($array as $element) OR @foreach($array as $key => $element)
 *
 * - @endforeach = End of a foreach loop, remember to use @foreach before you use this statement!
 *
 * - @part(filename) = includes the filename with default DIR at "src/funkphp/pages/parts/"
 *
 *
 *
 */

// Always include &$c so you can use the processed data,
// now when you are finally at the end of the Request!
return function (&$c) {};
