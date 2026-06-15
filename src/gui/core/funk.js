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

/**
 * Extracts and normalizes data configurations across current UI input values
 */
function grabcURLTestDataFromUI() {
	const method = document.getElementById('curl-method').value;
	const url = document.getElementById('curl-url').value.trim();
	const rawHeaders = document.getElementById('curl-headers').value;
	const contentTypeHeader = document.getElementById('curl-content-type').value;
	const acceptHeader = document.getElementById('curl-accept').value;
	const hostOverride = document.getElementById('curl-host-override').value.trim();
	const rawPayload = document.getElementById('curl-payload').value.trim();

	// 1. Process manually typed headers from the textarea
	let headersArray = rawHeaders
		.split('\n')
		.map((line) => line.trim())
		.filter((line) => line.length > 0);

	// 2. Safety Layer: Strip manual duplicates to let dropdown definitions win
	if (contentTypeHeader) {
		headersArray = headersArray.filter((h) => !h.toLowerCase().startsWith('content-type:'));
		headersArray.push(`Content-Type: ${contentTypeHeader}`);
	}

	if (acceptHeader) {
		headersArray = headersArray.filter((h) => !h.toLowerCase().startsWith('accept:'));
		headersArray.push(`Accept: ${acceptHeader}`);
	}

	// SMART INJECTION: If the developer filled out the Host Override field,
	// push it directly into the headers array seamlessly!
	if (hostOverride.length > 0) {
		// Prevent duplicate Host headers if they already typed one manually
		headersArray = headersArray.filter((h) => !h.toLowerCase().startsWith('host:'));
		headersArray.push(`Host: ${hostOverride}`);
	}

	// Attempt to safely process payload context blocks if active
	let payload = null;
	if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method) && rawPayload.length > 0) {
		try {
			// If valid JSON object, parse it so it can pass downstream safely
			payload = JSON.parse(rawPayload);
		} catch (e) {
			// Fallback cleanly to text mapping if parsing string fails
			payload = rawPayload;
		}
	}

	console.log({ method: method, headers: headersArray, url: url, payload: payload });

	return {
		method: method,
		headers: headersArray,
		url: url,
		payload: payload,
	};
}

/**
 * Fires dynamic HTTP requests directly down to the local FunkGUI interceptor file path
 */
function testFunkGUIcURL() {
	// 1. Compile current dynamic form contexts
	const requestPayloadData = grabcURLTestDataFromUI();

	if (!requestPayloadData.url) {
		alert('Please specify a valid destination target URL endpoint.');
		return;
	}

	// Capture destination terminal container window handle references
	const consoleLogOutput = document.getElementById('test-results');
	consoleLogOutput.textContent = 'Processing network round-trip stream proxy requests...';

	// 2. Fetch directly towards the current file directory root path
	fetch(window.location.pathname, {
		method: 'POST',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
			'X-Funk-Intent': 'gui_test_runner', // Security signature protection
		},
		body: JSON.stringify(requestPayloadData),
	})
		.then((response) => {
			if (!response.ok) {
				throw new Error(`HTTP network runtime error status received: ${response.status}`);
			}
			return response.json();
		})
		.then((data) => {
			// Render a clean, stylized visual breakdown inside your existing console box output
			consoleLogOutput.innerHTML = `
            <div class="text-[#89b4fa] font-bold">[HTTP STATUS]: ${data.status}</div>
            <div class="text-slate-500 text-xs my-1">--- RAW RESPONSE BODY ---</div>`;

			// Depending if JSON or HTML, append innerHTML as real HTML if it is HTML, otherwise escape it as text to prevent layout breaking
			if (data?.headers['Content-Type']?.includes('text/html')) {
				consoleLogOutput.innerHTML += `
				${data.body || 'No HTML body content returned.'}`;
				return;
			}
			consoleLogOutput.innerHTML = `
            <pre class="bg-[#11111b] p-2 rounded text-[#a6e3a1] whitespace-pre-wrap break-all">${escapeHtml(data.body || 'No textual data payload returned.')}</pre>`;
		})
		.catch((error) => {
			console.error('Error communicating with FunkGUI Proxy:', error);
			consoleLogOutput.innerHTML = `<span class="text-[#f38ba8]">❌ Connection Failed: ${error.message}</span>`;
		});
}

/**
 * Utility method to prevent text components from breaking inner layout blocks
 */
function escapeHtml(text) {
	return text
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}

// Clears the div id="terminal-console" window
function clearCLI() {
	const consoleBox = document.getElementById('terminal-console');
	consoleBox.innerHTML = 'Terminal CLI Window Cleared!';
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
