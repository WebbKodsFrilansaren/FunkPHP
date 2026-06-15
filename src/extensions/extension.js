const vscode = require('vscode');
const path = require('path');

function activate(context) {
	// Show simple popup at bottom right corner to show that the extension
	// has been loaded into VSCode/ium!
	vscode.window.showInformationMessage('FunkPHP Extension Activated!');
	let provider = vscode.languages.registerCompletionItemProvider(
		'php',
		{
			provideCompletionItems(document, position, token, context) {
				// 1. Get the absolute path of the current active file
				const absoluteFilePath = document.uri.fsPath;

				// 2. Normalize paths to handle slash variants across OS platforms
				const normalizedPath = absoluteFilePath.split(path.sep).join('/');

				// --- CONTEXT GUARD 1: VALIDATION FOLDER ---
				if (normalizedPath.includes('/src/funkphp/validation/')) {
					const linePrefix = document.lineAt(position).text.substr(0, position.character);
					if (!linePrefix.includes('|') && !linePrefix.includes('"')) return undefined;

					// Return ONLY validation-specific rule suggestions here
					return getValidationRules();
				}

				// --- CONTEXT GUARD 2: SQL QUERY FOLDER ---
				if (normalizedPath.includes('/src/funkphp/sql/')) {
					// Return ONLY SQL tables and JOIN configuration syntax suggestions
					return getSqlSchemaSuggestions();
				}

				// --- CONTEXT GUARD 3: TEMPLATE ENGINE / PAGES ---
				if (normalizedPath.includes('/src/funkphp/pages/')) {
					const linePrefix = document.lineAt(position).text.substr(0, position.character);

					// Only autocomplete if they specifically typed "{{vd." or "{{d."
					if (!linePrefix.endsWith('vd.') && !linePrefix.endsWith('d.')) {
						return undefined;
					}

					// Return frontend template variables harvested from compiled files
					return getTemplateVariables();
				}

				// Fallback: If they are in core/ functions / or outside the app structure, do nothing.
				return undefined;
			},
		},
		'.',
		'|',
		'{'
	); // Added '|' and '{' as custom trigger characters!

	context.subscriptions.push(provider);
}

// Helper functions to keep your code clean
function getValidationRules() {
	return [new vscode.CompletionItem('required', vscode.CompletionItemKind.Keyword)];
}
function getSqlSchemaSuggestions() {
	return [new vscode.CompletionItem('JOINS_ON', vscode.CompletionItemKind.Property)];
}
function getTemplateVariables() {
	return [new vscode.CompletionItem('user_id', vscode.CompletionItemKind.Variable)];
}

module.exports = { activate, deactivate: () => {} };
