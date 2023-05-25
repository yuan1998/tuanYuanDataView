<template>
    <el-table :data="cloneData"
              ref="tableRef"
              row-key="owner"
              :default-sort="{ prop: 'count', order: 'descending' }"
              border stripe style="width: 100%;height: 100%;">
        <el-table-column prop="owner" label="现场咨询" width="150"/>
        <el-table-column prop="count" sortable label="接诊" width="80"/>
        <el-table-column label="顾客"
        >
            <template #header>
                <el-select
                    v-model="search"
                    multiple
                    clearable
                    placeholder="客户类型"
                    style="width: 100%;"
                    @change="typeFilterMethod"
                >
                    <el-option
                        v-for="item in filters"
                        :key="item"
                        :label="item"
                        :value="item"
                    />
                </el-select>
            </template>
            <template #default="scope">
                <div v-if="scope.row.customers">
                    <span v-for="(item,index) in scope.row.customers" :key="index" style="margin-left: 20px;display: inline-block;"><strong style="color:red;font-size: 16px;">{{
                            item.name
                        }}</strong> <span style="font-size: 14px;">({{ item.type }})</span></span>
                </div>
            </template>
        </el-table-column>
    </el-table>
</template>
<script setup>
import {defineProps, ref} from 'vue';
import {cloneDeep} from 'lodash'

const {tableData} = defineProps(["tableData"]);
const tableRef = ref()
const search = ref()
const cloneData = ref(cloneDeep(tableData));

const typeKeys = tableData.reduce((a, current) => {
    return a.concat(current['customers'].map(i => i['type']));
}, []);


function onlyUnique(value, index, array) {
    return array.indexOf(value) === index;
}

const filters = typeKeys.filter(onlyUnique);

const typeFilterMethod = (value) => {
    console.log("tableData", tableData);
    cloneData.value = cloneDeep(tableData)
        .map((item) => {
            if (value.length)
                item.customers = item.customers.filter((customerInfo) => {
                    return value.includes(customerInfo.type)
                });
            item.count = item.customers?.length || 0;
            return item;
        }).filter((item) => {
            return item.customers?.length;
        })
}
</script>
<style scoped lang="less">

</style>
