<script setup lang="ts">
import {
  NavigationMenu,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  navigationMenuTriggerStyle,
} from "@/components/ui/navigation-menu"
import { Link } from "@inertiajs/vue3";
import { markRaw } from "vue";

const link = markRaw(Link);
</script>

<template>
  <NavigationMenu>
    <NavigationMenuList>
      <NavigationMenuItem>
        <NavigationMenuLink
            :href="route('home')"
            :class="navigationMenuTriggerStyle()"
            :as="link"
            :active="$page.component === 'Home'"
        >
          Store
        </NavigationMenuLink>
      </NavigationMenuItem>
      <NavigationMenuItem>
        <NavigationMenuLink
            :href="route('settings')"
            :class="navigationMenuTriggerStyle()"
            :as="link"
            :active="$page.component === 'Settings'"
        >
          Settings
        </NavigationMenuLink>
      </NavigationMenuItem>
    </NavigationMenuList>
  </NavigationMenu>
</template>