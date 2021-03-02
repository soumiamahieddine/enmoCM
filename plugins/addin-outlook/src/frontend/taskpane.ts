/*
 * Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 * See LICENSE in the project root for license information.
 */

import { enableProdMode } from "@angular/core";
import { platformBrowserDynamic } from "@angular/platform-browser-dynamic";
import { AppModule } from "./app/app.module";
import { environment } from './environments/environment';

console.log(Office);

Office.initialize = () => {
  console.log("Office.context :");
  console.log( Office.context );
  console.log("Office...hostVersion :");
  console.log( Office.context.mailbox.diagnostics.hostVersion );
  if (environment.production) {
      enableProdMode();
    }
  platformBrowserDynamic().bootstrapModule(AppModule)
    .catch(err => console.error(err));
};

