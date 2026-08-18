import BillDataService from "../../Services/BillDataService";

export default {
  name: "ArchivedBills",
  data() {
    return {
      bills: [],
      loading: true,
      page: 1,
      totalPages: 1,
      total: 0,
      searchText: "",
    };
  },
  methods: {
    async fetchArchived() {
      this.loading = true;
      try {
        let url = `?page=${this.page}`;
        if (this.searchText) {
          url += `&search_text=${encodeURIComponent(this.searchText)}`;
        }
        const res = await BillDataService.getArchive(url);
        const data = res.data;
        if (data.data) {
          this.bills = data.data;
          this.totalPages = data.meta?.last_page || data.last_page || 1;
          this.total = data.meta?.total || data.total || data.data.length;
        }
      } catch (e) {
        console.error("Failed to fetch archived bills", e);
      }
      this.loading = false;
    },
    goToPage(p) {
      if (p >= 1 && p <= this.totalPages) {
        this.page = p;
        this.fetchArchived();
      }
    },
    onSearch() {
      this.page = 1;
      this.fetchArchived();
    },
    formatDate(d) {
      if (!d) return "—";
      return new Date(d).toLocaleString();
    },
    checkBillStatus(status) {
      const map = {
        1: "REQUESTED", 2: "PENDING", 3: "APPROVED FROM CEO",
        4: "CANCELED", 5: "APPROVED FROM ADMIN", 6: "Printed & Closed",
        7: "Delivered to Finances", 8: "ARCHIVED",
      };
      return map[status] || status;
    },
  },
  created() {
    this.fetchArchived();
  },
};
