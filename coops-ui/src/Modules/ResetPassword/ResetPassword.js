import Button from '@/Components/Button/Button.vue';
import http from '@/http-common';

export default {
    name: "ResetPassword",
    components: {
        'app-button': Button
    },
    data() {
        return {
            password: '',
            password_confirmation: '',
            isLoading: false,
            errorMessage: '',
            successMessage: '',
            done: false,
        };
    },
    computed: {
        token() {
            return this.$route.query.token || '';
        },
        email() {
            return this.$route.query.email || '';
        }
    },
    mounted() {
        document.body.classList.add('login-page');
        if (!this.token || !this.email) {
            this.errorMessage = 'Invalid reset link. Please request a new one.';
        }
    },
    unmounted() {
        document.body.classList.remove('login-page');
    },
    methods: {
        async handleReset() {
            this.errorMessage = '';
            this.successMessage = '';

            if (this.password !== this.password_confirmation) {
                this.errorMessage = 'Passwords do not match.';
                return;
            }

            if (this.password.length < 8) {
                this.errorMessage = 'Password must be at least 8 characters.';
                return;
            }

            this.isLoading = true;

            try {
                const res = await http.post('/reset-password', {
                    email: this.email,
                    token: this.token,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                });
                this.successMessage = res.data.message || 'Password reset successfully.';
                this.done = true;
            } catch (err) {
                if (err.response && err.response.data) {
                    const data = err.response.data;
                    if (data.errors) {
                        // Validation errors
                        const firstError = Object.values(data.errors)[0];
                        this.errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                    } else {
                        this.errorMessage = data.message || 'Something went wrong. Please try again.';
                    }
                } else {
                    this.errorMessage = 'Something went wrong. Please try again.';
                }
            } finally {
                this.isLoading = false;
            }
        }
    }
};
