/**
 * StudentCreator - Handles unique student email generation via GAM API
 */
export class StudentCreator {
    constructor() {
        this.form = document.getElementById('student-form');
        this.firstNameInput = document.getElementById('firstName');
        this.lastNameInput = document.getElementById('lastName');
        this.gradeInput = document.getElementById('grade');
        this.generateButton = document.getElementById('generate-button');
        this.resultContainer = document.getElementById('result-container');
        this.resultContent = document.getElementById('result-content');
        this.emailDisplay = document.getElementById('email-display');
        this.generatedEmailInput = document.getElementById('generated-email');
        this.copyButton = document.getElementById('copy-button');
        this.copyFeedback = document.getElementById('copy-feedback');

        this.init();
    }

    init() {
        if (!this.form) {
            console.error('Student form not found');
            return;
        }

        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.copyButton.addEventListener('click', () => this.copyToClipboard());
    }

    calculateGradYear(curGrade) {
        const currentYear = new Date().getFullYear();
        return currentYear + 12 - curGrade;
    }

    generateBaseEmail(firstName, lastName, grade) {
        const gradYear = this.calculateGradYear(grade);
        const gradYearShort = String(gradYear).slice(-2);
        const firstInitial = firstName.charAt(0).toLowerCase();
        const lastNameFormatted = lastName.charAt(0).toLowerCase() + lastName.slice(1).toLowerCase();
        return `wm${gradYearShort}${firstInitial}${lastNameFormatted}`;
    }

    async checkEmailExists(email) {
        try {
            const response = await fetch('/api/gam/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                    document.querySelector('input[name="_token"]')?.value
                },
                body: JSON.stringify({ email })
            });

            if (!response.ok) {
                console.error('API response not ok:', response.status);
                throw new Error(`API error: ${response.status}`);
            }

            const data = await response.json();
            console.log(`Checking ${email}: exists = ${data.exists}`);
            return data.exists === true;
        } catch (error) {
            console.error('Error checking email:', error);
            throw error;
        }
    }

    async findUniqueEmail(baseEmail) {
        const domain = '@westmschools.org';
        let increment = 0;
        let email = baseEmail + domain;

        while (await this.checkEmailExists(email)) {
            increment++;
            email = baseEmail + increment + domain;
        }

        return email;
    }

    async handleSubmit(e) {
        e.preventDefault();

        const firstName = this.firstNameInput.value.trim();
        const lastName = this.lastNameInput.value.trim();
        const grade = parseInt(this.gradeInput.value, 10);

        if (!firstName || !lastName || isNaN(grade)) {
            this.showResult('error', 'Please fill in all fields');
            return;
        }

        this.setLoading(true);
        this.showResult('loading', 'Generating unique email...');
        this.emailDisplay.classList.add('hidden');

        try {
            const baseEmail = this.generateBaseEmail(firstName, lastName, grade);
            const uniqueEmail = await this.findUniqueEmail(baseEmail);

            this.showResult('success', 'Email generated successfully');
            this.displayEmail(uniqueEmail);

        } catch (error) {
            console.error('Error generating email:', error);
            this.showResult('error', 'Error generating email. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }

    setLoading(isLoading) {
        this.generateButton.disabled = isLoading;
        this.firstNameInput.disabled = isLoading;
        this.lastNameInput.disabled = isLoading;
        this.gradeInput.disabled = isLoading;
    }

    showResult(type, message) {
        this.resultContainer.classList.remove('hidden');
        this.resultContent.className = `p-4 rounded-lg ${type}`;

        if (type === 'loading') {
            this.resultContent.innerHTML = `<span class="spinner"></span>${message}`;
        } else {
            this.resultContent.textContent = message;
        }
    }

    displayEmail(email) {
        this.emailDisplay.classList.remove('hidden');
        this.generatedEmailInput.value = email;
        this.copyFeedback.classList.add('hidden');
    }

    async copyToClipboard() {
        const email = this.generatedEmailInput.value;
        if (!email) return;

        try {
            await navigator.clipboard.writeText(email);
            this.copyFeedback.classList.remove('hidden');
            setTimeout(() => {
                this.copyFeedback.classList.add('hidden');
            }, 2000);
        } catch (error) {
            console.error('Failed to copy:', error);
            this.generatedEmailInput.select();
            document.execCommand('copy');
            this.copyFeedback.classList.remove('hidden');
            setTimeout(() => {
                this.copyFeedback.classList.add('hidden');
            }, 2000);
        }
    }
}
