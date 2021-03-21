# Doppelkopf

Doppelkopf on BGA

## Try here

https://studio.boardgamearena.com/lobby?game=3060

# Dokumentation

## JS

### Constants

- `DIAMOND` (0) to `TRUMP` (4)
- `NINE` (0) to `ACE` (5)
- `NORMAL` (0) to `SOLOACE` (7)

### gamedatas

```ts
type GameDatas = {
  players: {
    [id: playerid]: {
      id: playerid;
      score: string;
      color: string;
      name: string;
      zombie: number;
      eliminated: number;
      is_ai: string;
      beginner: boolean;
    };
  };
  wedding?: playerid
  foxes: ???[];
  doppelköpfe: ???[];
  cardSorting: {
    suit: string;
    value: string;
    trump: string;
  }[];
  hand: {
    id: string;
    suit: string;
    value: string;
  }[];
  // Cards on the Table
  table: {
    [id: string]: {
      id: string;
      type: string;
      type_arg: string;
      location: string;
      location_arg: string;
    };
  };
  throw: string;
};
```
