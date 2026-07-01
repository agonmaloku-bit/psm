<template src="./EmailNotification.html"></template>
<script src="./EmailNotification.js"></script>
<style scoped>
.notif-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.notif-section-title {
    font-size: 15px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
}
.notif-recipients-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.notif-select-all {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    white-space: nowrap;
    cursor: pointer;
    margin: 0;
}
.notif-search {
    max-width: 200px;
}
.notif-user-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
.notif-user-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    transition: background 0.15s;
}
.notif-user-item:hover {
    background: #f1f5f9;
}
.notif-user-item--selected {
    background: #eff6ff;
}
.notif-user-item:last-child {
    border-bottom: none;
}
.notif-user-info {
    display: flex;
    flex-direction: column;
}
.notif-user-name {
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
}
.notif-user-email {
    font-size: 12px;
    color: #64748b;
}
.notif-selected-count {
    margin-top: 10px;
    font-size: 13px;
    color: #334155;
}
</style>
