### 关于常量
```    
    statusOptions: [
      { value: null, text: '请选择' },
      { value: '0', text: '在库' },
      { value: '3', text: '使用中' }, 
      { value: '2', text: '审核通过' }, // 待分配
      { value: '5', text: '已报废' },   
      { value: '1', text: '待借出审批' },
      { value: '6', text: '待删除审批' },
      { value: '4', text: '待报废审批' },
    ],
    departOptions: [
      { value: null, text: '请选择' },
      { value: '29', text: 'DT部' },
      { value: '33', text: 'VT部' },
      { value: '37', text: 'SWT部' }
    ],
    sectionOptions: [
      { value: null, text: '请选择' },
      { value: '1884', text: 'SCD' },
      { value: '2271', text: 'SWV' },
      { value: '2272', text: 'PSD' },
      { value: '2273', text: 'CUD' },
      { value: '2274', text: 'FWD' },
      { value: '442', text: 'SYD' },
      { value: '462', text: 'HWD' },
      { value: '485', text: 'MED' },
      { value: '491', text: 'CSV' },
      { value: '499', text: 'HWV' },
      { value: '520', text: 'PAV' },
      { value: '540', text: 'SSD' },
```

### 关于按钮的权限
- 普通用户 {申请} {导出} 
- 样机管理员 {申请} {导出} {添加} {分配} {归还} {报废} {删除} {导入} {编辑}
- 样机审核员 {报废} {删除} 
- T-Manager {申请} {导出}
- S-Manager {申请} {导出}

### 关于侧边栏的权限
- 普通用户 {待归还}
- 样机管理员 {待分配} {待报废审批} {待删除审批}
- 样机审核员 {待报废审批}
- S-Manager {待借出审批} {待删除审批}
- T-Manager {待借出审批} {待删除审批}
