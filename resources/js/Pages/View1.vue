<template>
    <el-table :data="tableData"
              :default-sort="{ prop: 'count', order: 'descending' }"
              border stripe style="width: 100%;height: 100%;">
        <el-table-column prop="owner" label="现场咨询" width="150"/>
        <el-table-column prop="count" sortable label="接诊" width="50"/>
        <el-table-column prop="name" label="顾客"/>
    </el-table>
</template>
<script setup>
import {defineProps} from 'vue';
const {tableData} = defineProps(["tableData"]);
</script>
<style scoped lang="less">

</style>
