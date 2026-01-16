/**
 * ChromebookLookup - Handles Chromebook usage history lookup via GAM API
 */
export class ChromebookLookup {
    constructor() {
        this.form = document.getElementById('chromebook-form');
        this.searchInput = document.getElementById('searchQuery');
        this.resultLimitSlider = document.getElementById('resultLimit');
        this.limitValueDisplay = document.getElementById('limitValue');
        this.searchButton = document.getElementById('search-button');
        this.loadingOverlay = document.getElementById('loading-overlay');
        this.loadingMessage = document.getElementById('loading-message');
        this.resultsHistory = document.getElementById('results-history');

        this.init();
    }

    init() {
        if (!this.form) {
            console.error('Chromebook lookup form not found');
            return;
        }

        // Handle slider change
        this.resultLimitSlider.addEventListener('input', (e) => {
            this.limitValueDisplay.textContent = e.target.value;
        });

        // Handle form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    /**
     * Detect the type of search query
     * @param {string} query
     * @returns {'email'|'serial_or_asset'}
     */
    detectQueryType(query) {
        if (query.includes('@')) {
            return 'email';
        }
        return 'serial_or_asset';
    }

    async handleSubmit(e) {
        e.preventDefault();

        const query = this.searchInput.value.trim();
        const limit = parseInt(this.resultLimitSlider.value, 10);

        if (!query) {
            this.addErrorResult('Please enter a serial number, asset ID, or email');
            return;
        }

        const queryType = this.detectQueryType(query);

        if (queryType === 'email') {
            await this.searchByUser(query, limit);
        } else {
            // Try serial and asset searches
            await this.searchBySerialOrAsset(query, limit);
        }
    }

    async searchBySerialOrAsset(query, limit) {
        this.showLoading(`Searching for ${query}...`);

        try {
            // Try both serial and asset searches in parallel
            const [serialResponse, assetResponse] = await Promise.all([
                fetch('/api/gam/chromebook-by-serial', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken()
                    },
                    body: JSON.stringify({ serial_number: query, limit })
                }),
                fetch('/api/gam/chromebook-by-asset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken()
                    },
                    body: JSON.stringify({ asset_id: query, limit })
                })
            ]);

            const serialData = await serialResponse.json();
            const assetData = await assetResponse.json();

            const hasSerialResults = serialData.success && serialData.data && serialData.data.length > 0;
            const hasAssetResults = assetData.success && assetData.data && assetData.data.length > 0;

            if (hasSerialResults) {
                this.addSerialResult(query, serialData.data);
            }
            if (hasAssetResults) {
                this.addAssetResult(query, assetData.data);
            }
            if (!hasSerialResults && !hasAssetResults) {
                this.addErrorResult(`No cached data found for: ${query}`);
            }

        } catch (error) {
            console.error('Error searching:', error);
            this.addErrorResult('Error searching. Please try again.');
        } finally {
            this.hideLoading();
        }
    }


    async searchByUser(email, limit) {
        this.showLoading(`Searching for Chromebooks used by ${email}...`);

        try {
            const response = await fetch('/api/gam/chromebook-by-user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({ email, limit })
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                this.addUserResult(email, data.data);
            } else {
                this.addErrorResult(data.message || `No cached data found for user: ${email}`);
            }

        } catch (error) {
            console.error('Error searching by user:', error);
            this.addErrorResult('Error searching for Chromebooks. Please try again.');
        } finally {
            this.hideLoading();
        }
    }


    addSerialResult(serialNumber, users) {
        const card = document.createElement('div');
        card.className = 'p-4 border border-green-300 bg-green-50 rounded-lg';

        const header = document.createElement('div');
        header.className = 'flex items-center justify-between mb-3';

        const title = document.createElement('h4');
        title.className = 'font-medium text-green-800';
        title.innerHTML = `Recent users of <span class="font-mono">${serialNumber}</span>`;

        const timestamp = document.createElement('span');
        timestamp.className = 'text-xs text-green-600';
        timestamp.textContent = new Date().toLocaleTimeString();

        header.appendChild(title);
        header.appendChild(timestamp);
        card.appendChild(header);

        const list = document.createElement('div');
        list.className = 'space-y-2';

        users.forEach((user, index) => {
            const row = this.createResultRowWithTimestamp(index + 1, user.email, user.recorded_at, user.recorded_at_human);
            list.appendChild(row);
        });

        card.appendChild(list);
        this.resultsHistory.prepend(card);
    }

    addUserResult(email, chromebooks) {
        const card = document.createElement('div');
        card.className = 'p-4 border border-green-300 bg-green-50 rounded-lg';

        const header = document.createElement('div');
        header.className = 'flex items-center justify-between mb-3';

        const title = document.createElement('h4');
        title.className = 'font-medium text-green-800';
        title.innerHTML = `Chromebooks used by <span class="font-mono">${email}</span>`;

        const timestamp = document.createElement('span');
        timestamp.className = 'text-xs text-green-600';
        timestamp.textContent = new Date().toLocaleTimeString();

        header.appendChild(title);
        header.appendChild(timestamp);
        card.appendChild(header);

        const list = document.createElement('div');
        list.className = 'space-y-2';

        chromebooks.forEach((chromebook, index) => {
            const row = this.createResultRowWithTimestamp(index + 1, chromebook.serial_number, chromebook.recorded_at, chromebook.recorded_at_human);
            list.appendChild(row);
        });

        card.appendChild(list);
        this.resultsHistory.prepend(card);
    }

    addAssetResult(assetId, users) {
        const card = document.createElement('div');
        card.className = 'p-4 border border-green-300 bg-green-50 rounded-lg';

        const header = document.createElement('div');
        header.className = 'flex items-center justify-between mb-3';

        const title = document.createElement('h4');
        title.className = 'font-medium text-green-800';
        title.innerHTML = `Recent users of asset <span class="font-mono">${assetId}</span>`;

        const timestamp = document.createElement('span');
        timestamp.className = 'text-xs text-green-600';
        timestamp.textContent = new Date().toLocaleTimeString();

        header.appendChild(title);
        header.appendChild(timestamp);
        card.appendChild(header);

        const list = document.createElement('div');
        list.className = 'space-y-2';

        users.forEach((user, index) => {
            const row = this.createResultRowWithTimestamp(index + 1, user.email, user.recorded_at, user.recorded_at_human);
            list.appendChild(row);
        });

        card.appendChild(list);
        this.resultsHistory.prepend(card);
    }

    addErrorResult(message) {
        const card = document.createElement('div');
        card.className = 'p-4 border border-red-300 bg-red-50 rounded-lg';

        const header = document.createElement('div');
        header.className = 'flex items-center justify-between';

        const title = document.createElement('p');
        title.className = 'font-medium text-red-800';
        title.textContent = message;

        const timestamp = document.createElement('span');
        timestamp.className = 'text-xs text-red-600';
        timestamp.textContent = new Date().toLocaleTimeString();

        header.appendChild(title);
        header.appendChild(timestamp);
        card.appendChild(header);

        this.resultsHistory.prepend(card);
    }

    createResultRow(index, value) {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg bg-white';

        const numberSpan = document.createElement('span');
        numberSpan.className = 'text-sm text-gray-500 font-medium';
        numberSpan.textContent = `${index}.`;

        const valueSpan = document.createElement('span');
        valueSpan.className = 'flex-1 text-gray-900 font-mono';
        valueSpan.textContent = value;

        const copyButton = document.createElement('button');
        copyButton.type = 'button';
        copyButton.className = 'px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded transition-colors duration-200';
        copyButton.textContent = 'Copy';
        copyButton.addEventListener('click', () => this.copyToClipboard(value, copyButton));

        div.appendChild(numberSpan);
        div.appendChild(valueSpan);
        div.appendChild(copyButton);

        return div;
    }

    createResultRowWithTimestamp(index, value, rawTimestamp, humanTimestamp) {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg bg-white';

        const numberSpan = document.createElement('span');
        numberSpan.className = 'text-sm text-gray-500 font-medium';
        numberSpan.textContent = `${index}.`;

        const valueSpan = document.createElement('span');
        valueSpan.className = 'flex-1 text-gray-900 font-mono';
        valueSpan.textContent = value;

        const timestampWrapper = document.createElement('span');
        timestampWrapper.className = 'text-xs text-gray-500 text-right';

        // Format raw timestamp nicely
        const formattedDate = this.formatTimestamp(rawTimestamp);
        timestampWrapper.innerHTML = `${formattedDate}<br><span class="text-gray-400">(${humanTimestamp})</span>`;

        const copyButton = document.createElement('button');
        copyButton.type = 'button';
        copyButton.className = 'px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded transition-colors duration-200';
        copyButton.textContent = 'Copy';
        copyButton.addEventListener('click', () => this.copyToClipboard(value, copyButton));

        div.appendChild(numberSpan);
        div.appendChild(valueSpan);
        div.appendChild(timestampWrapper);
        div.appendChild(copyButton);

        return div;
    }

    formatTimestamp(timestamp) {
        if (!timestamp) return '';
        const date = new Date(timestamp);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    parseSerialOutput(output) {
        // Parse GAM info cros output for recentUsers
        // Format:
        //   recentUsers
        //     type: USER_TYPE_MANAGED
        //       email: student@domain.org
        const results = [];
        const lines = output.split('\n');

        let inRecentUsers = false;
        let currentUser = {};

        for (const line of lines) {
            const trimmedLine = line.trim();

            // Check for recentUsers section (with or without colon)
            if (trimmedLine === 'recentUsers' || trimmedLine === 'recentUsers:') {
                inRecentUsers = true;
                continue;
            }

            if (inRecentUsers) {
                if (trimmedLine.startsWith('type:')) {
                    currentUser.type = trimmedLine.replace('type:', '').trim();
                } else if (trimmedLine.startsWith('email:')) {
                    currentUser.email = trimmedLine.replace('email:', '').trim();
                    if (currentUser.email) {
                        results.push({ ...currentUser });
                    }
                    currentUser = {};
                } else if (trimmedLine && !trimmedLine.startsWith('type:') && !trimmedLine.startsWith('email:')) {
                    // Check if we've hit a new top-level section (not indented)
                    if (!line.startsWith('  ') && !line.startsWith('\t')) {
                        inRecentUsers = false;
                    }
                }
            }
        }

        return results;
    }

    parseUserOutput(output, limit) {
        const results = [];
        const lines = output.split('\n').filter(line => line.trim());

        if (lines.length < 2) {
            return results;
        }

        const headers = this.parseCSVLine(lines[0]);
        const serialIndex = headers.findIndex(h => h.toLowerCase() === 'serialnumber');

        if (serialIndex === -1) {
            return results;
        }

        const maxRows = Math.min(lines.length - 1, limit);
        for (let i = 1; i <= maxRows; i++) {
            const values = this.parseCSVLine(lines[i]);
            if (values.length > serialIndex && values[serialIndex]) {
                results.push({
                    serialNumber: values[serialIndex]
                });
            }
        }

        return results;
    }

    parseCSVLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;

        for (let i = 0; i < line.length; i++) {
            const char = line[i];

            if (char === '"') {
                inQuotes = !inQuotes;
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }

        result.push(current.trim());
        return result;
    }

    copyToClipboard(text, button) {
        // Try modern clipboard API first, fall back to execCommand
        const copyText = () => {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            } else {
                // Fallback for non-secure contexts (HTTP)
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                return new Promise((resolve, reject) => {
                    document.execCommand('copy') ? resolve() : reject();
                    textArea.remove();
                });
            }
        };

        copyText().then(() => {
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.remove('bg-green-600', 'hover:bg-green-700');
            button.classList.add('bg-gray-500');
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('bg-gray-500');
                button.classList.add('bg-green-600', 'hover:bg-green-700');
            }, 1500);
        }).catch((error) => {
            console.error('Failed to copy:', error);
            button.textContent = 'Failed';
            setTimeout(() => {
                button.textContent = 'Copy';
            }, 1500);
        });
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ||
               document.querySelector('input[name="_token"]')?.value;
    }

    showLoading(message) {
        this.loadingMessage.textContent = message;
        this.loadingOverlay.classList.remove('hidden');
        this.searchButton.disabled = true;
    }

    hideLoading() {
        this.loadingOverlay.classList.add('hidden');
        this.searchButton.disabled = false;
    }
}
