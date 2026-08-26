<?php // FunkCLI Command `help` - recreating Default Folders & Files

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
cli_info_without_exit("Attempting to `Restore Default Files` and `Folders` in FunkPHP");
if (function_exists("cli_restore_default_folders_and_files") && is_callable('cli_restore_default_folders_and_files')) {
    cli_restore_default_folders_and_files();
    cli_info("Any `Recreated File/Folder` will be shown above. If You are reading this, the Command Successfully Ran!");
} else {
    cli_err('For some reason the FunkCLI Function `cli_restore_default_folders_and_files()` does NOT exist or is NOT Callable? You can try Restore by using your (if any) Version Controlling for your Project OR redownload/reclone the FunkPHP Project from any of its Available Official Repositories.');
}
