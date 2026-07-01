import ContentHeader from "../../Modules/Main/ContentHeader/ContentHeader.vue";
import BreadcrumbItem from "../../Modules/Main/BreadcrumbItem/BreadcrumbItem.vue";
import Content from "../../Modules/Main/Content/Content.vue";
import http from "../../http-common";
import AuthHeader from "../../Services/AuthHeader";

export default {
    name: "EmailNotification",
    components: {
        "content-header": ContentHeader,
        "breadcrumb-item": BreadcrumbItem,
        "page-content": Content,
    },
    data() {
        return {
            loading: true,
            sending: false,
            users: [],
            selectedUserIds: [],
            subject: "",
            body: "",
            selectAll: false,
            searchQuery: "",
            result: null, // { success, message }
        };
    },
    computed: {
        filteredUsers() {
            if (!this.searchQuery) return this.users;
            const q = this.searchQuery.toLowerCase();
            return this.users.filter(u =>
                (u.first_name + " " + u.last_name + " " + u.email).toLowerCase().includes(q)
            );
        },
        canSend() {
            return this.selectedUserIds.length > 0 && this.subject.trim() && this.body.trim();
        },
    },
    watch: {
        selectAll(val) {
            if (val) {
                this.selectedUserIds = this.filteredUsers.map(u => u.id);
            } else {
                this.selectedUserIds = [];
            }
        },
    },
    mounted() {
        this.fetchUsers();
    },
    methods: {
        async fetchUsers() {
            this.loading = true;
            try {
                const res = await http.get("/admin/notifications/users", { headers: AuthHeader() });
                const d = res.data && res.data.data ? res.data.data : res.data;
                this.users = Array.isArray(d) ? d : (d.data || []);
            } catch (e) {
                this.users = [];
            } finally {
                this.loading = false;
            }
        },
        toggleUser(userId) {
            const idx = this.selectedUserIds.indexOf(userId);
            if (idx === -1) {
                this.selectedUserIds.push(userId);
            } else {
                this.selectedUserIds.splice(idx, 1);
            }
        },
        isSelected(userId) {
            return this.selectedUserIds.includes(userId);
        },
        async send() {
            if (!this.canSend) return;
            this.sending = true;
            this.result = null;
            try {
                const res = await http.post("/admin/notifications/send", {
                    user_ids: this.selectedUserIds,
                    subject: this.subject,
                    body: this.body,
                }, { headers: AuthHeader() });
                const d = res.data && res.data.data ? res.data.data : res.data;
                this.result = { success: true, message: d.message || `Sent to ${d.sent} recipients.` };
                this.subject = "";
                this.body = "";
                this.selectedUserIds = [];
                this.selectAll = false;
            } catch (e) {
                const msg = (e.response && e.response.data && e.response.data.message) || "Failed to send.";
                this.result = { success: false, message: msg };
            } finally {
                this.sending = false;
            }
        },
    },
};
