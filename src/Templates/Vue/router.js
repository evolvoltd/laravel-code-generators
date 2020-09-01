const routes = [
  {
    path: '/dummykcs',
    name: 'dummys',
    component: () => import(/* webpackChunkName: "dummys" */ '@/views/dummys/Dummys'),
    beforeEnter: roleGuard,
    meta: { allowedRoles: ['admin'] },
    children: [
      {
        path: 'create',
        name: 'createDummy',
        component: () => import(/* webpackChunkName: "createDummy" */ '@/views/dummys/CreateDummy'),
        beforeEnter: roleGuard,
        meta: { allowedRoles: ['admin'] },
      },
      {
        path: ':id/edit',
        name: 'editDummy',
        component: () => import(/* webpackChunkName: "editDummy" */ '@/views/dummys/EditDummy'),
        beforeEnter: roleGuard,
        meta: { allowedRoles: ['admin'] },
      },
    ],
  },
];
