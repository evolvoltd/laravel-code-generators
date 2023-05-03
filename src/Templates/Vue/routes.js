const routes = [
  {
    path: '/dummykcs',
    name: 'dummys',
    component: () => import(/* webpackChunkName: "dummys" */ '@/views/dummykcs/Dummys'),
    beforeEnter: roleGuard,
    meta: { allowedRoles: ['admin'] },
    children: [
      {
        path: 'create',
        name: 'createDummy',
        component: () => import(/* webpackChunkName: "createDummy" */ '@/views/dummykcs/CreateDummy'),
        beforeEnter: roleGuard,
        meta: { allowedRoles: ['admin'] },
      },
      {
        path: ':id/edit',
        name: 'editDummy',
        component: () => import(/* webpackChunkName: "editDummy" */ '@/views/dummykcs/EditDummy'),
        beforeEnter: roleGuard,
        meta: { allowedRoles: ['admin'] },
      },
    ],
  },
];
