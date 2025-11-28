import React from 'react';

/* blueprint/import *//* RustserverwiperImportStart */import RustserverwiperXaypembypd from '@blueprint/extensions/rustserverwiper/RustWipeContainer';/* RustserverwiperImportEnd */

interface RouteDefinition {
  path: string;
  name: string | undefined;
  component: React.ComponentType;
  exact?: boolean;
  adminOnly: boolean | false;
  identifier: string;
}
interface ServerRouteDefinition extends RouteDefinition {
  permission: string | string[] | null;
}
interface Routes {
  account: RouteDefinition[];
  server: ServerRouteDefinition[];
}

export default {
  account: [
    /* routes/account *//* RustserverwiperAccountRouteStart *//* RustserverwiperAccountRouteEnd */
  ],
  server: [
    /* routes/server *//* RustserverwiperServerRouteStart */{ path: '/wipe', permission: null, name: 'Rust Wipe', component: RustserverwiperXaypembypd, adminOnly: false, identifier: 'rustserverwiper' },/* RustserverwiperServerRouteEnd */
  ],
} as Routes;
