# Addressing Plugin for GLPI — English Documentation

## Overview

The **Addressing** plugin lets you manage and visualise IP address ranges in GLPI.
It generates reports showing, for every IP in a given network, whether it is assigned, free, reserved or duplicated. It also provides IP reservation, automatic ping checking and report export.

- **License:** GPLv2+
- **Repository:** <https://github.com/pluginsGLPI/addressing>

---

## Installation

1. Copy the `addressing` folder into the `marketplace/` directory of your GLPI instance.
2. Go to **Setup → Plugins** and click **Install**, then **Enable**.

The installation automatically creates the required SQL tables and registers a scheduled task (`UpdatePing`).

---

## Uninstallation

In **Setup → Plugins**, click **Uninstall**.
All plugin tables and associated rights are removed.

---

## Rights

The plugin adds an **IP Addressing** tab to the **Administration → Profiles** page.

| Right | Description |
|---|---|
| `plugin_addressing` | Read, create, edit, delete IP ranges (READ / CREATE / UPDATE / DELETE / PURGE) |
| `plugin_addressing_use_ping_in_equipment` | Display ping result on an equipment form |

---

## Configuration

Access the configuration page via **Tools → IP Addressing → (settings icon)** or
**Setup → Plugins → Addressing → Configure**.

| Setting | Description |
|---|---|
| **Show assigned IPs** | Include IPs already assigned to an asset in the report |
| **Show free IPs** | Include IPs with no associated asset |
| **Show reserved IPs** | Include reserved IPs (`reserv-*` port) |
| **Show duplicate IPs** | Include IPs found on more than one asset |
| **Use ping** | Enable the ping function to detect active free IPs |
| **Ping system** | Choose the operating system used to perform pings |

> **Note:** On GLPI Cloud, the ping function is automatically disabled.

---

## Usage

### Accessing the module

The module is accessible via **Tools → IP Addressing** for profiles with `plugin_addressing` ≥ READ.

### Creating an IP range

1. Click **Add**.
2. Fill in the fields:
   - **Name**: descriptive label for the range (e.g. `Office network`)
   - **First IP** / **Last IP**: range boundaries (e.g. `192.168.1.1` / `192.168.1.254`)
   - **Network** *(optional)*: link to a GLPI network
   - **Location** *(optional)*: associated GLPI location
   - **FQDN** *(optional)*: associated DNS domain
   - **VLAN** *(optional)*: associated VLAN
   - **Show assigned / free / reserved / duplicate IPs**: checkboxes to control the default report display
   - **Use ping**: enable to automatically ping free IPs

3. Click **Save**.

### Generating a report

Open an existing IP range and click the **Report** tab.

The table lists every IP in the range with:

| Column | Content |
|---|---|
| IP address | Numbered IP in the range |
| Asset | Name of the associated asset (clickable link) |
| Network port | Port name |
| MAC | MAC address |
| User | User assigned to the asset |
| Type | Asset type (Computer, Printer, etc.) |

**Colour legend:**

| Colour | Meaning |
|---|---|
| Normal background | Assigned IP |
| Light green background | Free IP (no asset) |
| Orange background | Reserved IP |
| Red background | Duplicate IP |
| Green icon ✓ | Ping successful (active IP) |
| Red icon ✗ | No ping response (inactive IP) |

### Report filters

The **Filters** tab of each IP range lets you create sub-ranges to narrow the report scope. Each filter has:

- **Name**
- **Entity**: limits the report to assets in that entity
- **Type**: limits to a specific asset type
- **First IP** / **Last IP**: sub-range

In the Report tab, select the desired filter from the drop-down list and click **Search**.

### IP reservation

In the Report tab, click **Reserve** next to a free IP to reserve that address.
The form requires:

| Field | Description |
|---|---|
| **Object name** | Name of the asset to create or find |
| **Type** | Asset type (Computer, Network Equipment…) |
| **Entity** | GLPI entity |
| **Location** | GLPI location *(optional)* |
| **Status** | GLPI status *(optional)* |
| **FQDN** | DNS domain *(optional)* |
| **MAC** | MAC address *(optional)* |
| **Comment** | Free-form comment |

A network port named `reserv-<IP>` is automatically created on the asset.

### IP comments

A text comment can be added to any IP in the report.
Comments are stored in the `glpi_plugin_addressing_ipcomments` table.

### Ping on the equipment form

If the `plugin_addressing_use_ping_in_equipment` right is enabled for a profile, a **Ping** block appears on every compatible asset form (Computer, Printer, etc.).

It displays the last known result (OK / KO) and the date of the last check.

---

## Scheduled task (CRON)

The plugin registers the `UpdatePing` task (default frequency: daily).

It iterates over all IP ranges with **Use ping** enabled and updates results in the `glpi_plugin_addressing_pinginfos` table.

You can also trigger ping manually from the Report tab using the **Manual launch of ping** button.

---

## Report export

From the Report tab, use the export links at the top of the paginated table to export in:

- **PDF**
- **CSV**
- **SLK** (spreadsheet)

---

## Massive actions

Users with the `UPDATE` right can apply massive actions to the IP range list. The available action is:

- **Transfer**: move selected ranges to another GLPI entity.

---

## DataInjection integration

The plugin integrates with the **Data Injection** plugin to allow bulk import of IP ranges via CSV file.

---

## Database tables

| Table | Description |
|---|---|
| `glpi_plugin_addressing_addressings` | IP ranges |
| `glpi_plugin_addressing_filters` | Filters linked to ranges |
| `glpi_plugin_addressing_pinginfos` | Ping results per IP |
| `glpi_plugin_addressing_configs` | Global plugin configuration |
| `glpi_plugin_addressing_ipcomments` | IP comments |

---

## Global search options

The plugin adds the **Ping result** search option to all compatible asset types (Computers, Network Equipment, Peripherals, Phones, Printers, Enclosures, PDUs, Clusters).
This option is available in the standard GLPI search engine under identifier `5000`.
