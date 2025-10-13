<?php // GLOBAL BLOCK OF EXACTLY MATCHED IPs
// This filtering occurs AFTER valid method check and BEFORE UA check!
// Syntax to add an exactly matched IP:
// The array key is the IP to block. The value can be an empty array or a reason.
// '1.2.3.4' => ['reason' => 'Known Scraper'], // IPv4 Example
// '2001:db8::1' => [],                       // IPv6 Example

return [];
