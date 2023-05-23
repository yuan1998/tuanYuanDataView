<template>
    <el-table :data="tableData"
              :default-sort="{ prop: 'count', order: 'descending' }"
              border stripe style="width: 100%;height: 100%;">
        <el-table-column prop="owner" label="现场咨询" width="150"/>
        <el-table-column prop="count" sortable label="接诊" width="80"/>
        <el-table-column label="顾客">
            <template #default="scope">
                <div v-if="scope.row.customers">
                    <span v-for="(item,index) in scope.row.customers" :key="index" style="margin-left: 20px;display: inline-block;"><strong style="color:red;font-size: 16px;">{{ item.name }}</strong> <span style="font-size: 14px;">({{item.type}})</span></span>
                </div>
            </template>
        </el-table-column>
    </el-table>
</template>
<script setup>
import {defineProps} from 'vue';
const {tableData} = defineProps(["tableData"]);
</script>
<style scoped lang="less">

</style>
