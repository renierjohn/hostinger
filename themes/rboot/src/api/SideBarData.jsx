import { ComponentBaseSideBarData } from '../redux/store';

const data =
  [
    {
      'id': 'card_group',
      'name': 'Card Group',
      'group': 0,
      'icon': 'https://cdn.icon-icons.com/icons2/1904/PNG/512/layout_121258.png'
    },
    {
      'id': 'card_simple',
      'name': 'Card Simple',
      'group': 1,
      'icon': 'https://static.thenounproject.com/png/19113-200.png'
    },
    {
      'id': 'card_advance',
      'name': 'Card Advance',
      'group': 1,
      'icon': 'https://cdn-icons-png.flaticon.com/512/1188/1188567.png'
    },
    {
      'id': 'accordion',
      'name': 'Accordion',
      'group': 1,
      'icon': 'https://static.thenounproject.com/png/1814255-200.png'
    },
    {
      'id': 'group',
      'name': 'Group',
      'group': 0,
      'icon': 'https://www.svgrepo.com/show/309668/group-list.svg'
    },
    {
      'id': 'slider',
      'name': 'Slider',
      'group': 1,
      'icon': 'https://cdn.icon-icons.com/icons2/1456/PNG/512/mbriimageslider_99582.png'
    },
    {
      'id': 'slider_group',
      'name': 'Slider Group',
      'group': 0,
      'icon': 'https://cdn.icon-icons.com/icons2/1456/PNG/512/mbriimageslider_99582.png'
    },
    {
      'id': 'banner_cta',
      'name': 'Banner CTA',
      'group': 1,
      'icon': 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSoAhZLDWoOAP8LpCY9fFW7rvjP_X-_vh5YTOxPUim-Xw&s'
    }
  ]

export const SideBarData = ComponentBaseSideBarData(data);

export default {
    SideBarData,
}
