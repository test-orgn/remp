import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { FormsModule } from '@angular/forms';

import { NgModule } from '@angular/core';
import { MdSlideToggleModule, MdToolbarModule, MdSelectModule, MdGridListModule, MdCardModule } from '@angular/material';
import 'hammerjs';

import { AppComponent } from './app.component';
import { HudComponent } from './hud/hud.component';
import { ArticlesComponent } from './articles/articles.component';
import { StatsComponent } from './stats/stats.component';
import { HttpClientModule } from '@angular/common/http';

@NgModule({
  declarations: [
    AppComponent, HudComponent, ArticlesComponent, StatsComponent
  ],
  imports: [
    BrowserModule, BrowserAnimationsModule, FormsModule,
    MdSlideToggleModule, MdToolbarModule, MdSelectModule, MdGridListModule, MdCardModule,
    HttpClientModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})

export class AppModule {
  constructor() {
  }
}
