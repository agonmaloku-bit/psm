import Button from '@/Components/Button/Button.vue';
import http from '@/http-common';

export default {
    name: "ForgotPassword",
    components: {
        'app-button': Button
    },
    data() {
        return {
            email: '',
            isLoading: false,
            errorMessage: '',
            successMessage: '',
            sent: false,
        };
    },
    mounted() {
        document.body.classList.add('login-page');
    },
    unmounted() {
        document.body.classList.remove('login-page');
    },
    methods: {
        async handleSubmit() {
            this.errorMessage = '';
            this.successMessage = '';
            this.isLoading = true;

            try {
                const res = await http.post('/forgot-password', { email: this.email });
                this.successMessage = res.data.message || 'Reset link sent.';
                this.sent = true;
            } catch (err) {
                if (err.response && err.response.status === 429) {
                    this.errorMessage = 'Please wait before requesting another reset link.';
                } else if (err.response && err.response.data && err.response.data.message) {
                    this.errorMessage = err.response.data.message;
                } else {
                    this.errorMessage = 'Something went wrong. Please try again.';
                }
            } finally {
                this.isLoading = false;
            }
        }
    }
};
