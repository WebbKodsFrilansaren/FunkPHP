<?php // FunkCLI Command `php funk extract-data`
// EXTRACT-DATA-FUNCTION: Extract data parts from files things as `
// in order to test something ASAP instead of having to run it through the main pipeline!

/**
 * -----------------------
 * FUNKCLI DEFAULT COMMAND
 * -----------------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU UNDERSTAND IT IN AND OUT.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
$singleArray = [];
$folderFilePath = cli_get_cli_input_from_interactive_or_regular($args, 'extract:data', 'folder_file_path');
$exactPath = (PROJECT_DIR . '/' . join('/', (explode(',', $folderFilePath))) . '.php');
if (!is_file($exactPath) || !is_readable($exactPath)) {
    cli_err("Folder+File Path `{$exactPath} NOT FOUND or NOT READABLE. Review Folder+File Path Permissions!`");
}
cli_info_without_exit("Exact Folder+File Path `{$exactPath}` FOUND & IS READABLE!");
$regex = cli_get_cli_input_from_interactive_or_regular($args, 'extract:data', 'extract_regex');
$regex = trim($regex, " \t\n\r\0\x0B\"'"); // Strip leading/trailing whitespace and quotes (' or ") due to arg-wrapping needs in terminal
// Ensure the 'm' (multiline) modifier is attached to the regex delimiter
if (preg_match('/^([\/#~@]).*\1([a-z]*)$/i', $regex, $flagMatches)) {
    $currentFlags = $flagMatches[2];
    if (!str_contains($currentFlags, 'm')) {
        $regex .= 'm';
    }
} else {
    // If user passed a pattern without delimiters (e.g. 'public function'), wrap it safely with 'm'
    $regex = '/' . preg_quote($regex, '/') . '/m';
}
$content = file_get_contents($exactPath);
if (!$content || trim($content) === '') {
    cli_err("File Content for `{$exactPath}` is Empty or Unreadable. Review File Permissions!");
}
try {
    if (@preg_match($regex, '') === false) {
        cli_err("Invalid provided Regex String `{$regex}` when tested with an Empty String!");
    }
} catch (Exception $e) {
    cli_err("Invalid provided Regex String `{$regex}` when tested with an Empty String!");
}
// Either we match and output each array OR we just show entire content without
if (preg_match_all($regex, $content, $MATCHES)) {
    $count = count($MATCHES) - 1;
    cli_success_without_exit("REGEX `{$regex}` FOUND `{$count} MATCHES` FOR `{$exactPath}`:");
    foreach ($MATCHES as $idx => $MATCH) {
        if ($idx === 0) continue; // We skip full match since the idea is we only wants parts of matched!
        echo (str_replace(["\n", "\r"], '', trim(var_export($MATCH, true)))) . ";\n";
    }
    echo "\n";
    cli_success("REGEX `{$regex}` FOUND `{$count} MATCHES` FOR `{$exactPath}` ABOVE! - You can select and Copy&Paste it as a Single Array from here!");
} else {
    cli_info("Valid Regex `{$regex}` for loaded `$exactPath` Content returned 0 Matches!");
}
