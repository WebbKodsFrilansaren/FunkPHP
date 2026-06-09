document.addEventListener('DOMContentLoaded', () => {
	const tabs = document.querySelectorAll('.tab-btn');
	const contents = document.querySelectorAll('.tab-content');

	tabs.forEach((tab) => {
		tab.addEventListener('click', () => {
			// Remove active states from all tabs and panels
			tabs.forEach((t) => t.classList.remove('active'));
			contents.forEach((c) => c.classList.remove('active'));

			// Activate the current tab button and target panel
			tab.classList.add('active');
			const target = tab.getAttribute('data-target');
			document.getElementById(target).classList.add('active');
		});
	});
});

async function executeCliCommand(
	commandString,
	args = { arg1: null, arg2: null, arg3: null, arg4: null, arg5: null, arg6: null }
) {
	const consoleBox = document.getElementById('terminal-console');

	// Match payload layout expected by src/cli/funk regexes
	const payload = {
		command: commandString,
		...args,
	};
	try {
		// Request points directly to this file, but specifies JSON intent
		const response = await fetch('/funkgui/', {
			method: 'POST',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(payload),
		});

		const rawText = await response.text();
		console.log(rawText);

		try {
			// Prettify the output if it's structural valid JSON
			const parsedJson = JSON.parse(rawText);
			consoleBox.innerHTML = buildFormattedOutputTerminalBox(parsedJson);
		} catch {
			// Fall back to displaying raw string output if text/formatting bled through
			consoleBox.innerText = rawText;
		}
	} catch (err) {
		consoleBox.innerText = 'Network Gateway Communication Error: ' + err.message;
	}
}

// Cleanly maps ANSI/CLI message intents directly into beautiful HTML elements
function buildFormattedOutputTerminalBox(parsedJson) {
	if (!parsedJson || !Array.isArray(parsedJson.messages)) {
		return `<div style="color: #94a3b8; font-family: monospace;">⚠️ No structural status messages returned.</div>`;
	}
	return parsedJson.messages
		.map((msg) => {
			let badgeBg = '#64748b';
			let badgeText = '#ffffff';
			let messageColor = '#f8fafc';

			switch (msg.type) {
				case 'SUCCESS':
					badgeBg = '#10b981';
					break;
				case 'ERROR':
				case 'SYNTAX_ERROR':
					badgeBg = '#ef4444';
					break;
				case 'WARNING':
					badgeBg = '#f59e0b';
					boxTextColor = '#1e293b';
					badgeText = '#1e293b';
					break;
				case 'INFO':
				case 'IMPORTANT':
					badgeBg = '#3b82f6';
					break;
			}
			// Returns a Flexbox row container with two dedicated grid divs
			return `
    <div style="display: flex; align-items: center; font-family: monospace; line-height: 1.5; color: ${messageColor};">
        <p style="background: ${badgeBg}; color: ${badgeText}; padding: 8px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85em; margin-right: 12px; min-width: 169px; text-align: center; align-self:flex-start;">
            [FunkCLI - ${msg.type}]
        </p>
        <p style="word-break: break-word; text-align: left; margin: 0;">
            ${msg.message}
        </p>
    </div>
        `;
		})
		.join('');
}
// 1. Catches the keyboard event from the HTML input box
function handleCommandInput(event) {
	if (event.key === 'Enter') {
		sendCli_command_input(event.target);
	}
}
// 2. Processes the text string, formats the arguments, and fires the gateway payload
function sendCli_command_input(inputElement) {
	const rawLineText = inputElement.value.trim();
	if (!rawLineText) return; // Do nothing if they hit Enter on an empty line
	// Split the text string by any sequence of spaces
	const tokens = rawLineText.split(/\s+/);
	// The very first word is always the core command (e.g., "recompile", "new:r")
	const commandString = tokens[0];
	// Map the subsequent words to your sequential CLI payload layout (arg1 up to arg6)
	const payloadArgs = {
		arg1: tokens[1] || null,
		arg2: tokens[2] || null,
		arg3: tokens[3] || null,
		arg4: tokens[4] || null,
		arg5: tokens[5] || null,
		arg6: tokens[6] || null,
	};

	// Clear out the input bar instantly to mimic a true console prompt reset
	inputElement.value = '';
	// Recycle your existing, bulletproof executor function!
	executeCliCommand(commandString, payloadArgs);
}
